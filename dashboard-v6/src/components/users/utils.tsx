import { Modal } from "antd";
import type { IAccountForm } from "./SignUp";
import { post } from "../../request";
import type { ISignInResponse, ISignUpRequest } from "../../api/Auth";

export const onSignIn = async (token: string, values: IAccountForm) => {
  if (values.password !== values.password2) {
    //TODO remove Modal
    Modal.error({ title: "两次密码不同" });
    return false;
  }
  const url = "/api/v2/sign-up";
  const data = {
    token: token,
    username: values.username,
    nickname:
      values.nickname && values.nickname.trim() !== ""
        ? values.nickname
        : values.username,
    email: values.email,
    password: values.password,
    lang: values.lang,
  };
  console.info("api request", url, data);
  const signUp = await post<ISignUpRequest, ISignInResponse>(
    "/api/v2/sign-up",
    data
  );
  console.info("api response", signUp);
  return signUp;
};
