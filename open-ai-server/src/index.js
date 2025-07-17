import logger from "./logger";
import server from "./server";
import config from "./config";

const port = config["port"];
server.listen(port, () => {
  logger.info("Server is running on port %d", port);
  logger.info("Health check: http://0.0.0.0:%d/health", port);
  logger.info("API endpoint: http://0.0.0.0:%d/api/openai", port);
  logger.info("API Server Url: %s", config["api_url"]);
});
