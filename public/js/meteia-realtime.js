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
      this.openReplyDestination = '/temp-queue/meteia-live-view-open';

      const socketUrl = new URL(url, window.location.href);
      socketUrl.search = '';
      this.socket = new WebSocket(socketUrl);
      this.socket.onopen = () => this.connect();
      this.socket.onmessage = (event) => this.receive(String(event.data));
      this.socket.onerror = () => console.error('Realtime connection failed.');
      this.socket.onclose = (event) => {
        this.stopHeartbeat();
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
        'heart-beat': '25000,25000',
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
        this.startHeartbeat(lines);
        this.open();
        return;
      }

      if (command === 'MESSAGE') {
        if (this.readyState !== this.OPEN) {
          this.receiveOpenReply(body);
          return;
        }

        this.dispatch('message', new MessageEvent('message', {data: body}));
        return;
      }

      if (command === 'RECEIPT') {
        const receiptId = stompHeader(lines, 'receipt-id');
        if (receiptId === this.subscriptionReceipt) {
          this.readyState = this.OPEN;
          this.dispatch('open', new Event('open'));
          return;
        }

        return;
      }

      if (command === 'ERROR') {
        const message = stompErrorMessage(lines, body);
        console.error('Realtime connection rejected:', message);
        this.close();
      }
    }

    startHeartbeat(lines) {
      this.stopHeartbeat();

      const serverHeartbeat = stompHeader(lines, 'heart-beat') || '0,0';
      const [, serverWantsIncoming] = serverHeartbeat.split(',').map((value) => Number.parseInt(value, 10) || 0);
      const interval = Math.max(25000, serverWantsIncoming);
      if (interval <= 0) {
        return;
      }

      this.heartbeat = window.setInterval(() => {
        if (this.socket.readyState === WebSocket.OPEN) {
          this.socket.send('\n');
        }
      }, interval);
    }

    stopHeartbeat() {
      if (this.heartbeat !== undefined) {
        window.clearInterval(this.heartbeat);
        this.heartbeat = undefined;
      }
    }

    receiveOpenReply(body) {
      let payload;
      try {
        payload = JSON.parse(body);
      } catch (error) {
        console.error('Realtime open response was not valid JSON.', error);
        this.close();
        return;
      }

      if (typeof payload.error === 'string') {
        console.error('Realtime connection rejected:', payload.error);
        this.close();
        return;
      }

      if (typeof payload.destination === 'string' && payload.destination !== '') {
        this.subscribe(payload.destination);
        return;
      }

      if (typeof payload.queue === 'string' && payload.queue !== '') {
        this.subscribe('/amq/queue/' + payload.queue);
        return;
      }

      console.error('Realtime open response did not include a queue destination.');
      this.close();
    }

    subscribe(destination) {
      this.socket.send(frame('SUBSCRIBE', {
        id: 'meteia-live-view',
        destination: destination,
        receipt: this.subscriptionReceipt,
      }, ''));
    }

    open() {
      this.socket.send(frame('SEND', {
        destination: this.options.openDestination,
        'reply-to': this.openReplyDestination,
      }, JSON.stringify({
        token: this.options.token,
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
      openDestination: required(params, 'open'),
      username: required(params, 'stomp_user'),
      password: required(params, 'stomp_passcode'),
      vhost: required(params, 'stomp_vhost'),
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
