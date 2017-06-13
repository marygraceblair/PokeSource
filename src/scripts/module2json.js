'use strict';

if (process.argv[2] === undefined) {
    throw new Error('Missing SOURCE_FILE argument: "' + JSON.stringify(process.argv.join(' ')) + '"');
}

let mod = require(process.argv[2]);

process.stdout.write(JSON.stringify(mod));