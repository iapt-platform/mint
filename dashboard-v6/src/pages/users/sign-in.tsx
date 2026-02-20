import React, { useState } from "react";
import { LoginForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";
import { UserOutlined, LockOutlined } from "@ant-design/icons";
import { useNavigate } from "react-router-dom";

interface loginRequest {
  username: string;
  password: string;
}
const fakeLogin = async (values: loginRequest) => {
  return new Promise<boolean>((resolve) => {
    setTimeout(() => {
      if (values.username === "admin" && values.password === "123456") {
        resolve(true);
      } else {
        resolve(false);
      }
    }, 1200);
  });
};

const Login: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const handleSubmit = async (values: loginRequest) => {
    setLoading(true);
    const ok = await fakeLogin(values);
    setLoading(false);

    if (ok) {
      message.success("登录成功");
      navigate("/dashboard");
    } else {
      message.error("用户名或密码错误");
    }
  };

  return (
    <div
      style={{
        height: "100vh",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        background:
          "linear-gradient(135deg,#f0f5ff 0%, #ffffff 50%, #f0f5ff 100%)",
      }}
    >
      <LoginForm
        title="登陆"
        subTitle="wikipali 巴利语文献编辑平台"
        onFinish={handleSubmit}
        submitter={{
          searchConfig: {
            submitText: "登录",
          },
          submitButtonProps: {
            loading,
            style: { width: "100%" },
          },
        }}
        style={{
          width: 320,
          borderRadius: 12,
          boxShadow: "0 4px 20px rgba(0,0,0,0.08)",
          background: "#fff",
          padding: 24,
        }}
      >
        <ProFormText
          name="username"
          fieldProps={{
            size: "large",
            prefix: <UserOutlined />,
          }}
          placeholder="用户名/邮箱"
          rules={[{ required: true, message: "请输入用户名/邮箱" }]}
        />

        <ProFormText.Password
          name="password"
          fieldProps={{
            size: "large",
            prefix: <LockOutlined />,
          }}
          placeholder="密码"
          rules={[{ required: true, message: "请输入密码" }]}
        />
      </LoginForm>
    </div>
  );
};

export default Login;
