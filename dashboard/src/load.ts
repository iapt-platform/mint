//import { Empty } from "google-protobuf/google/protobuf/empty_pb";
//import { Duration } from "google-protobuf/google/protobuf/duration_pb";

import { get as getToken, IUser, signIn } from "./reducers/current-user";
//import { DURATION } from "./reducers/current-user";
import { ISite, refresh as refreshLayout } from "./reducers/layout";
import { get } from "./request";
//import { GRPC_HOST,  grpc_metadata } from "./request";
import store from "./store";

interface IUserResponse {
	ok: boolean;
	message: string;
	data: IUser;
}
const init = () => {
	console.log("onload");
	// ajax get site information, SEE reducers/layout/ISite
	get("/v2/siteinfo/en").then((json) => {
		store.dispatch(refreshLayout(json as ISite));
	});
	const token = getToken();
	if (token) {
		// get current user profile & new token, SEE reducers/current-user/IUser
		get<IUserResponse>("/v2/auth/current").then((user) => {
			if (user.ok) {
				store.dispatch(signIn([user.data, token ? token : ""]));
			} else {
				console.error(user.message);
			}
		});
	} else {
		console.log("no token");
	}
};

export default init;
