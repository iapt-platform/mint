import { Metadata } from "grpc-web";
import { get as getLocale } from "./locales";

import { get as getToken } from "./reducers/current-user";

export const backend = (u: string) => `/api${u}`;

export const GRPC_HOST: string =
  import.meta.env.VITE_APP_GRPC_HOST || "http://localhost:9999";

export const grpc_metadata = (): Metadata => {
  return {
    authorization: `Bearer ${getToken()}`,
    "accept-language": getLocale() || "en-US",
  };
};

export const upload = () => {
  return {
    Authorization: `Bearer ${getToken()}`,
  };
};

export const options = (method: string): RequestInit => {
  return {
    credentials: "include",
    headers: {
      Authorization: `Bearer ${getToken()}`,
      "Content-Type": "application/json; charset=utf-8",
    },
    mode: "cors",
    method,
  };
};

export const get = async <R>(path: string): Promise<R> => {
  const response = await fetch(backend(path), options("GET"));
  const res: R = await response.json();
  return res;
};

export const delete_ = async <R>(path: string): Promise<R> => {
  const response = await fetch(backend(path), options("DELETE"));
  const res: R = await response.json();
  return res;
};

// https://github.github.io/fetch/#options
export const post = async <Q, R>(path: string, body: Q): Promise<R> => {
  const data = options("POST");
  data.body = JSON.stringify(body);
  const response = await fetch(backend(path), data);
  const res: R = await response.json();
  return res;
};

export const patch = <Request, Response>(
  path: string,
  body: Request
): Promise<Response> => {
  const data = options("PATCH");
  data.body = JSON.stringify(body);
  return fetch(backend(path), data).then((res) => {
    if (res.status === 200) {
      return res.json();
    }
    throw res.text();
  });
};

export const put = <Request, Response>(
  path: string,
  body: Request
): Promise<Response> => {
  const data = options("PUT");
  data.body = JSON.stringify(body);
  return fetch(backend(path), data).then((res) =>
    res.status === 200
      ? res.json()
      : res.json().then((err) => {
          throw err;
        })
  );
};

export const download = (path: string, name: string) => {
  const data = options("GET");
  fetch(backend(path), data)
    .then((response) => response.blob())
    .then((blob) => {
      var url = window.URL.createObjectURL(blob);
      var a = document.createElement("a");
      a.href = url;
      a.download = name;
      document.body.appendChild(a); // for firefox
      a.click();
      a.remove();
    });
};
