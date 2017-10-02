'use strict';

// Converts a JS module file into JSON

if (process.argv[2] === undefined) {
    throw new Error('Missing SOURCE_FILE argument: "' + JSON.stringify(process.argv.join(' ')) + '"');
}

let mod = require(process.argv[2]);
let str = mod ? JSON.stringify(mod) : '{}';

process.stdout.write(str ? str : '{}');