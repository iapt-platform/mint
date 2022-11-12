//import { Empty } from "google-protobuf/google/protobuf/empty_pb";
//import { Duration } from "google-protobuf/google/protobuf/duration_pb";

import { get as getToken, IUser, signIn } from "./reducers/current-user";
//import { DURATION } from "./reducers/current-user";
import { ISite, refresh as refreshLayout } from "./reducers/layout";
import { get, IErrorResponse } from "./request";
//import { GRPC_HOST,  grpc_metadata } from "./request";
import store from "./store";

interface IUserResponse {
	ok: boolean;
	message: string;
	data: IUser;
}

export interface ISiteInfoResponse {
	title: string;
}
interface IUserData {
	nickName: string;
	realName: string;
	avatar: string;
	roles: string[];
	token: string;
}
export interface ITokenRefreshResponse {
	ok: boolean;
	message: string;
	data: IUserData;
}

const init = () => {
	get<ISiteInfoResponse | IErrorResponse>("/v2/siteinfo/en").then(
		(response) => {
			if ("title" in response) {
				const it: ISite = {
					title: response.title,
					subhead: "",
					keywords: [],
					description: "",
					copyright: "",
					logo: "",
					author: { name: "", email: "" },
				};
				store.dispatch(refreshLayout(it));
			}
		}
	);
	const token = getToken();
	if (token) {
		get<ITokenRefreshResponse | IErrorResponse>("/v2/auth/current").then(
			(response) => {
				console.log(response);
				if ("data" in response) {
					const it: IUser = {
						nickName: response.data.nickName,
						realName: response.data.realName,
						avatar: response.data.avatar,
						roles: response.data.roles,
					};
					store.dispatch(signIn([it, response.data.token]));
				}
			}
		);
	} else {
		console.log("no token");
	}
};

export default init;
