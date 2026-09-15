import express, { type Express, type Request, type Response } from "express";
import dotenv from "dotenv";
import { pinoHttp } from "pino-http";

import { heartbeat } from "./handlers/index.ts";

dotenv.config();

const app: Express = express();
const PORT = process.env.PORT || 3000;

const logging = pinoHttp({});
logging.logger.level = process.env.LOG_LEVEL || "info";
app.use(express.json());
app.use(logging);

app.get("/heartbeat", heartbeat);
app.get("/", (req: Request, res: Response) => {
  req.log.debug("Hello");

  res.status(200).json({ message: "Hello from TypeScript Express!" });
});

app.listen(PORT, () => {
  logging.logger.info(`Server is running at http://localhost:${PORT}`);
});
