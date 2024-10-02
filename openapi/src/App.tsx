import SwaggerUI from "swagger-ui-react";

import "swagger-ui-react/swagger-ui.css";

import "./App.css";

const Widget = () => (
  <SwaggerUI
    url={`${import.meta.env.BASE_URL}/assets/protocol/main.yaml`}
    withCredentials
  />
);

export default Widget;
