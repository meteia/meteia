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
      this.bindings = new LiveBindings(this);

      const socketUrl = new URL(url, window.location.href);
      socketUrl.search = '';
      this.socket = new WebSocket(socketUrl);
      this.socket.onopen = () => this.connect();
      this.socket.onmessage = (event) => this.receive(String(event.data));
      this.socket.onerror = () => console.error('Realtime connection failed.');
      this.socket.onclose = (event) => {
        this.stopHeartbeat();
        this.bindings.stop();
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
      this.bindings.stop();
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
          this.bindings.start();
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

    sendAdjust(add, remove) {
      if (this.readyState !== this.OPEN || this.socket.readyState !== WebSocket.OPEN) {
        return;
      }
      if (this.options.adjustDestination === '') {
        return;
      }
      const payload = JSON.stringify({
        token: this.options.token,
        add: add,
        remove: remove,
      });
      this.socket.send(frame('SEND', {
        destination: this.options.adjustDestination,
        'content-type': 'application/json',
      }, payload));
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

  class LiveBindings {
    constructor(socket) {
      this.socket = socket;
      this.counts = new Map();
      this.pendingAdd = new Set();
      this.pendingRemove = new Set();
      this.scheduled = false;
      this.observer = null;
      this.boundFlush = () => this.flush();
    }

    start() {
      this.seed(document.body);
      this.observer = new MutationObserver((records) => this.onMutations(records));
      this.observer.observe(document.body, {childList: true, subtree: true});
    }

    stop() {
      if (this.observer !== null) {
        this.observer.disconnect();
        this.observer = null;
      }
      this.counts.clear();
      this.pendingAdd.clear();
      this.pendingRemove.clear();
      this.scheduled = false;
    }

    seed(root) {
      this.forEachLiveNode(root, (topic) => {
        const previous = this.counts.get(topic) || 0;
        this.counts.set(topic, previous + 1);
      });
    }

    onMutations(records) {
      for (const record of records) {
        record.addedNodes.forEach((node) => this.added(node));
        record.removedNodes.forEach((node) => this.removed(node));
      }
    }

    added(node) {
      this.forEachLiveNode(node, (topic) => this.increment(topic));
    }

    removed(node) {
      this.forEachLiveNode(node, (topic) => this.decrement(topic));
    }

    increment(topic) {
      const previous = this.counts.get(topic) || 0;
      this.counts.set(topic, previous + 1);
      if (previous === 0) {
        this.pendingRemove.delete(topic);
        this.pendingAdd.add(topic);
        this.schedule();
      }
    }

    decrement(topic) {
      const previous = this.counts.get(topic) || 0;
      const next = previous > 0 ? previous - 1 : 0;
      this.counts.set(topic, next);
      if (previous > 0 && next === 0) {
        this.pendingAdd.delete(topic);
        this.pendingRemove.add(topic);
        this.schedule();
      }
    }

    schedule() {
      if (this.scheduled) {
        return;
      }
      this.scheduled = true;
      window.requestAnimationFrame(this.boundFlush);
    }

    flush() {
      this.scheduled = false;
      if (this.pendingAdd.size === 0 && this.pendingRemove.size === 0) {
        return;
      }
      const add = Array.from(this.pendingAdd);
      const remove = Array.from(this.pendingRemove);
      this.pendingAdd.clear();
      this.pendingRemove.clear();
      this.socket.sendAdjust(add, remove);
    }

    forEachLiveNode(node, fn) {
      if (node === null || typeof node !== 'object') {
        return;
      }
      if (node.nodeType !== 1) {
        return;
      }
      if (node.hasAttribute && node.hasAttribute('data-live-bind')) {
        const topic = node.getAttribute('data-live-bind');
        if (topic !== null && topic !== '') {
          fn(topic);
        }
      }
      if (node.querySelectorAll) {
        const descendants = node.querySelectorAll('[data-live-bind]');
        for (const descendant of descendants) {
          const topic = descendant.getAttribute('data-live-bind');
          if (topic !== null && topic !== '') {
            fn(topic);
          }
        }
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
      tab: params.get('tab') || '',
      openDestination: required(params, 'open'),
      adjustDestination: params.get('adjust') || '',
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
