var FayeApp = (function() {

    var client = null;

    var EntryPoint = function(id) {
        this.id = id.replace(/\./g, '~');
    };
    EntryPoint.prototype.subscribe = function(channel) {
        channel = '/' + this.id + channel;
        return client.subscribe.apply(client, arguments);
    };
    EntryPoint.prototype.unsubscribe = function(channel) {
        channel = '/' + this.id + channel;
        return client.unsubscribe.apply(client, arguments);
    };
    EntryPoint.prototype.publish = function(channel) {
        channel = '/' + this.id + channel;
        return client.publish.apply(client, arguments);
    };

    var throwError = function() {
        throw new Error('Connection not established, please run FayeApp.connect({...})');
    };

    return {
        /**
         * @param config
         */
        connect: function(config) {
            client = new Faye.Client(config.url, config.options || {});
            client.addExtension({
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

            return client;
        },

        /**
         * @param event
         * @param callback
         */
        on: function(event, callback) {
            if (!client) { throwError(); }
            client.on.apply(client, arguments);
        },

        /**
         * @param extension
         */
        addExtension: function(extension) {
            if (!client) { throwError(); }
            client.addExtension.apply(client, arguments);
        },

        /**
         * @param extension
         */
        removeExtension: function(extension) {
            if (!client) { throwError(); }
            client.removeExtension.apply(client, arguments);
        },

        /**
         * @param id
         * @returns {EntryPoint}
         */
        createEntryPoint: function(id) {
            if (!client) { throwError(); }
            return new EntryPoint(id);
        }
    };
})();
