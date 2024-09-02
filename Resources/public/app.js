var FayeApp = (function() {

    function throwError() {
        throw new Error('Connection not established, please run FayeApp.init({...}).');
    }

    function EntryPoint(id, client) {
        this.id = id.replace(/\./g, '~');
        this.client = client;
    }

    EntryPoint.prototype.subscribe = function(channel) {
        channel = '/' + this.id + channel;
        return this.client.subscribe.apply(this.client, arguments);
    };

    EntryPoint.prototype.unsubscribe = function(channel) {
        channel = '/' + this.id + channel;
        return this.client.unsubscribe.apply(this.client, arguments);
    };

    EntryPoint.prototype.publish = function(channel) {
        channel = '/' + this.id + channel;
        return this.client.publish.apply(this.client, arguments);
    };

    function FayeApp() {
        this.config = null;
        this.client = null;
        this.anon = null;
    }

    /**
     * @param config
     * @returns {FayeApp}
     */
    FayeApp.prototype.init = function(config) {
        if (this.client) { throw new Error('Connection already established.'); }
        this.config = config;
        this.client = new Faye.Client(config.url, config.options || {});
        this.client.addExtension({
            outgoing: function(message, callback) {
                if (message.channel === '/meta/subscribe' || !message.channel.match(/^\/meta\//)) {
                    if (!message.ext) {
                        message.ext = {};
                    }
                    message.ext.security = config.security || {};
                }
                callback(message);
            }
        });
        return this;
    };
    FayeApp.prototype.connect = FayeApp.prototype.init;

    /**
     * @returns {FayeApp}
     */
    FayeApp.prototype.anonymous = function() {
        if (!this.client) { throwError(); }
        if (!this.anon) {
            this.anon = new FayeApp();
            var config = (JSON.parse(JSON.stringify(this.config)));
            if (config.security || false) {
                if (config.security['userIdentifier'] || false) {
                    delete config.security['userIdentifier'];
                }
            }
            this.anon.init(config);
        }
        return this.anon;
    };

    /**
     * @param event
     * @param callback
     * @returns {FayeApp}
     */
    FayeApp.prototype.addListener = function(event, callback) {
        if (!this.client) { throwError(); }
        this.client.addListener.apply(this.client, arguments);
        return this;
    };
    FayeApp.prototype.on = FayeApp.prototype.addListener;

    /**
     * @param event
     * @param callback
     * @returns {FayeApp}
     */
    FayeApp.prototype.removeListener = function(event, callback) {
        if (!this.client) { throwError(); }
        this.client.removeListener.apply(this.client, arguments);
        return this;
    };
    FayeApp.prototype.un = FayeApp.prototype.removeListener;

    /**
     * @param extension
     * @returns {FayeApp}
     */
    FayeApp.prototype.addExtension = function(extension) {
        if (!this.client) { throwError(); }
        this.client.addExtension.apply(this.client, arguments);
        return this;
    };

    /**
     * @param extension
     * @returns {FayeApp}
     */
    FayeApp.prototype.removeExtension = function(extension) {
        if (!this.client) { throwError(); }
        this.client.removeExtension.apply(this.client, arguments);
        return this;
    };

    /**
     * @param id
     * @returns {EntryPoint}
     */
    FayeApp.prototype.createEntryPoint = function(id) {
        if (!this.client) { throwError(); }
        return new EntryPoint((this.config['entry_point_prefix'] || '') + '@' + id, this.client);
    };

    var connections = {
        'default': new FayeApp()
    };

    connections['default'].connection = function(name) {
        if (connections.hasOwnProperty(name)) {
            return connections[name];
        }
        return connections[name] = new FayeApp();
    };

    return connections['default'];
})();
