import { GraphQLError } from "graphql";

import { get as get_token } from "./reducers/session";

export const upload = () => {
  return {
    Authorization: `Bearer ${get_token()}`,
  };
};

export const options = (method: string): RequestInit => {
  return {
    credentials: "include",
    headers: {
      Authorization: `Bearer ${get_token()}`,
      "Content-Type": "application/json; charset=utf-8",
    },
    mode: "cors",
    method,
  };
};

export const get = async <R>(path: string): Promise<R> => {
  const response = await fetch(path, options("GET"));
  const res: R = await response.json();
  return res;
};

export const delete_ = async <R>(path: string): Promise<R> => {
  const response = await fetch(path, options("DELETE"));
  const res: R = await response.json();
  return res;
};

export interface IGraphqlResponse<R> {
  data?: R;
  errors?: GraphQLError[];
}
export const graphql = async <Q, R>(
  query: string,
  variables: Q
): Promise<IGraphqlResponse<R>> => {
  const res: IGraphqlResponse<R> = await post("/graphql", { query, variables });
  return res;
};

// https://github.github.io/fetch/#options
export const post = async <Q, R>(path: string, body: Q): Promise<R> => {
  const data = options("POST");
  data.body = JSON.stringify(body);
  const response = await fetch(path, data);
  const res: R = await response.json();
  return res;
};

export const patch = <Request, Response>(
  path: string,
  body: Request
): Promise<Response> => {
  const data = options("PATCH");
  data.body = JSON.stringify(body);
  return fetch(path, data).then((res) => {
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
  return fetch(path, data).then((res) =>
    res.status === 200
      ? res.json()
      : res.json().then((err) => {
          throw err;
        })
  );
};

export const download = (path: string, name: string) => {
  const data = options("GET");
  fetch(path, data)
    .then((response) => response.blob())
    .then((blob) => {
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = name;
      document.body.appendChild(a); // for firefox
      a.click();
      a.remove();
    });
};
