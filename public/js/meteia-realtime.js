(function () {
  class StompSocket {
    constructor(url) {
      this.CONNECTING = 0;
      this.OPEN = 1;
      this.CLOSING = 2;
      this.CLOSED = 3;
      this.readyState = this.CONNECTING;
      this.binaryType = 'blob';
      this.onopen = null;
      this.onmessage = null;
      this.onerror = null;
      this.onclose = null;
      this.events = new EventTarget();
      this.buffer = '';
      this.options = optionsFrom(url);
      this.subscriptionReceipt = 'meteia-live-view-subscribe';
      this.openReceipt = 'meteia-live-view-open';

      const socketUrl = new URL(url, window.location.href);
      socketUrl.search = '';
      this.socket = new WebSocket(socketUrl);
      this.socket.onopen = () => this.connect();
      this.socket.onmessage = (event) => this.receive(String(event.data));
      this.socket.onerror = () => console.error('Realtime connection failed.');
      this.socket.onclose = (event) => {
        this.readyState = this.CLOSED;
        this.dispatch('close', closeEvent(event));
      };
    }

    addEventListener(type, listener, options) {
      this.events.addEventListener(type, listener, options);
    }

    removeEventListener(type, listener, options) {
      this.events.removeEventListener(type, listener, options);
    }

    dispatchEvent(event) {
      this.dispatch(event.type, event);
      return true;
    }

    send(_message) {}

    close(code, reason) {
      this.readyState = this.CLOSING;
      if (this.socket.readyState === WebSocket.OPEN) {
        this.socket.send(frame('DISCONNECT', {}, ''));
      }
      this.socket.close(code, reason);
    }

    connect() {
      this.socket.send(frame('CONNECT', {
        'accept-version': '1.2',
        host: this.options.vhost,
        login: this.options.username,
        passcode: this.options.password,
        'heart-beat': '0,0',
      }, ''));
    }

    receive(chunk) {
      this.buffer += chunk;
      let end = this.buffer.indexOf('\0');
      while (end !== -1) {
        const raw = this.buffer.slice(0, end).trimStart();
        this.buffer = this.buffer.slice(end + 1);
        if (raw !== '') {
          this.receiveFrame(raw);
        }
        end = this.buffer.indexOf('\0');
      }
    }

    receiveFrame(raw) {
      raw = raw.replace(/\r\n/g, '\n');
      const separator = raw.indexOf('\n\n');
      const head = separator === -1 ? raw : raw.slice(0, separator);
      const body = separator === -1 ? '' : raw.slice(separator + 2);
      const lines = head.split('\n');
      const command = lines[0];

      if (command === 'CONNECTED') {
        this.subscribe();
        this.readyState = this.OPEN;
        this.dispatch('open', new Event('open'));
        return;
      }

      if (command === 'MESSAGE') {
        this.dispatch('message', new MessageEvent('message', {data: body}));
        return;
      }

      if (command === 'RECEIPT') {
        const receiptId = stompHeader(lines, 'receipt-id');
        if (receiptId === this.subscriptionReceipt) {
          this.open();
          return;
        }

        if (receiptId === this.openReceipt) {
          console.debug('Realtime session opened.');
        }

        return;
      }

      if (command === 'ERROR') {
        const message = stompErrorMessage(lines, body);
        console.error('Realtime connection rejected:', message);
        if (message.includes("no queue '" + this.options.replyQueue + "'")) {
          window.location.reload();
        }
        this.close();
      }
    }

    subscribe() {
      this.socket.send(frame('SUBSCRIBE', {
        id: 'meteia-live-view',
        destination: '/queue/' + this.options.replyQueue,
        ack: 'auto',
        durable: 'false',
        'auto-delete': 'true',
        exclusive: 'false',
        receipt: this.subscriptionReceipt,
      }, ''));
    }

    open() {
      this.socket.send(frame('SEND', {
        destination: this.options.openDestination,
        'content-type': 'application/json',
        receipt: this.openReceipt,
        expiration: '5000',
        'x-meteia-command-id': this.options.commandId,
        'x-meteia-correlation-id': this.options.correlationId,
        'x-meteia-process-id': this.options.processId,
      }, JSON.stringify({
        token: this.options.token,
        replyQueue: this.options.replyQueue,
        topics: this.options.topics,
      })));
    }

    dispatch(type, event) {
      const wrapped = eventFor(type, event);
      this.events.dispatchEvent(wrapped);
      const handler = this['on' + type];
      if (typeof handler === 'function') {
        handler.call(this, eventFor(type, event));
      }
    }
  }

  function eventFor(type, event) {
    if (type === 'message') {
      return new MessageEvent('message', {data: event.data});
    }

    if (type === 'close') {
      return closeEvent(event);
    }

    if (type === 'error') {
      return new ErrorEvent('error', {message: event.message || 'Realtime connection failed.'});
    }

    return new Event(type);
  }

  function closeEvent(event) {
    return new CloseEvent('close', {
      code: event.code || 1000,
      reason: event.reason || '',
      wasClean: event.wasClean === true,
    });
  }

  function stompErrorMessage(lines, body) {
    const message = stompHeader(lines, 'message') || '';

    return [message, body || ''].filter((part) => part !== '').join(' ');
  }

  function stompHeader(lines, name) {
    const headers = {};
    for (const line of lines.slice(1)) {
      const split = line.indexOf(':');
      if (split !== -1) {
        headers[line.slice(0, split)] = line.slice(split + 1);
      }
    }

    return headers[name] || null;
  }

  function optionsFrom(url) {
    const params = new URL(url, window.location.href).searchParams;

    return {
      token: required(params, 'token'),
      replyQueue: required(params, 'reply'),
      openDestination: required(params, 'open'),
      commandId: required(params, 'command_id'),
      correlationId: required(params, 'correlation_id'),
      processId: required(params, 'process_id'),
      username: required(params, 'stomp_user'),
      password: required(params, 'stomp_passcode'),
      vhost: required(params, 'stomp_vhost'),
      topics: required(params, 'topics').split(',').filter((topic) => topic !== ''),
    };
  }

  function required(params, name) {
    const value = params.get(name);
    if (value === null || value === '') {
      throw new Error('Missing realtime option: ' + name);
    }

    return value;
  }

  function frame(command, headers, body) {
    const lines = [command];
    for (const [name, value] of Object.entries(headers)) {
      lines.push(name + ':' + String(value).replace(/[\r\n]/g, ''));
    }

    return lines.join('\n') + '\n\n' + body + '\0';
  }

  function install() {
    if (!window.htmx) {
      return;
    }

    const previous = window.htmx.createWebSocket || ((url) => new WebSocket(url));
    const create = (url) => {
      const parsed = new URL(url, window.location.href);
      if (parsed.pathname === '/stomp' && parsed.searchParams.has('token')) {
        return new StompSocket(parsed);
      }

      return previous(url);
    };

    window.htmx.createWebSocket = create;
    window.htmx.config.createWebSocket = create;
  }

  install();
})();
