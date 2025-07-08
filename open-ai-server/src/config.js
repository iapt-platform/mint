import progress from "node:process";
import fs from "node:fs";

import logger from "./logger";

if (progress.argv.length !== 3) {
  logger.error("USAGE: node main-XXX.js config.json");
  process.exit(1);
}

const config_file = progress.argv[2];
const config = JSON.parse(fs.readFileSync(config_file, "utf8"));
logger.info(`load config from ${config_file}`);
logger.debug("run on debug mode");

export default config;
