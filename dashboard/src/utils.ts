import { stringOrDate } from "react-big-calendar";

export function ApiFetch(url: string, method = "GET", data: any = null): Promise<Response> {
	const apiHost = process.env.REACT_APP_API_HOST;
	let body: string = "{}";
	if (data) {
		body = JSON.stringify(data);
	}
	return new Promise((resolve, reject) => {
		let apiUrl = apiHost + url;
		console.log("api", apiUrl);
		fetch(apiUrl, {
			method: method,
		})
			.then((response) => response.json())
			.then((response) => {
				resolve(response);
			})
			.catch((error) => {
				reject(error);
			});
	});
}

export function ApiGetText(url: stringOrDate): Promise<String> {
	const apiHost = "http://127.0.0.1:8000/api/";
	return new Promise((resolve, reject) => {
		let apiUrl = apiHost + url;
		console.log("api", apiUrl);
		fetch(apiUrl)
			.then((response) => response.text())
			.then((response) => {
				resolve(response);
			})
			.catch((error) => {
				reject(error);
			});
	});
}

export function PaliToEn(pali: string): string {
	let output: string = pali.toLowerCase();
	output = output.replaceAll(" ", "_");
	output = output.replaceAll("-", "_");
	output = output.replaceAll("ā", "a");
	output = output.replaceAll("ī", "i");
	output = output.replaceAll("ū", "u");
	output = output.replaceAll("ḍ", "d");
	output = output.replaceAll("ṭ", "t");
	output = output.replaceAll("ḷ", "l");
	return output;
}
