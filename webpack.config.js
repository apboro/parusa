const path = require('path');

module.exports = {
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            config: path.join(__dirname, 'resources/js/config', process.env.NODE_ENV)
        },
    },
};
