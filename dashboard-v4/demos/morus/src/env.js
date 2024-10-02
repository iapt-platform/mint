"use strict";

import {readFileSync} from "fs"

export class Config {
    constructor(file) {
        const raw = readFileSync(file);
        const it = JSON.parse(raw);
        this.port = it.port;
    }
}
