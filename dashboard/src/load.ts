import { Empty } from "google-protobuf/google/protobuf/empty_pb";
import { Duration } from "google-protobuf/google/protobuf/duration_pb";

import { DURATION, get as getToken, signIn } from "./reducers/current-user";
import { refresh as refreshLayout } from "./reducers/layout";
import { GRPC_HOST, get as httpGet, grpc_metadata } from "./request";
import store from "./store";

const init = () => {
  // TODO ajax get site information, SEE reducers/layout/ISite
  // store.dispatch(refreshLayout(it));

  if (getToken()) {
    // TODO get current user profile & new token, SEE reducers/current-user/IUser
    // store.dispatch(signIn([user, token]));
  }
};

export default init;
