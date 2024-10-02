export interface IUserApiResponse {
	id: string;
	name: string;
	avatar: string;
}

export interface IStudioApiResponse {
	id: string;
	name: string;
	avatar: string;
	owner: IUserApiResponse;
}
