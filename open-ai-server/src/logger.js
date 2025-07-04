import pino from "pino";
import pretty from "pino-pretty";

const stream = pretty({
  colorize: true,
});
const logger = pino(
  {
    level: "debug",
  },
  stream
);

export default logger;
