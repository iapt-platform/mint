import { Form, Input, Button, Select,Checkbox } from 'antd';
import { unstable_renderSubtreeIntoContainer } from 'react-dom';

export default () => {
    const [form] = Form.useForm();

    const onUsernameChange=(value:string)=>{
        console.log("username:",value);
    }
    const onGerderChange =(value:string)=>{
        switch(value){
            case 'male':
                form.setFieldsValue({note:'hi,man!'});
                return;
            case 'female':
                form.setFieldsValue({note:'hi,lady！'});
                return;
            case 'other':
                form.setFieldsValue({note:"hi there!"});
                return;
        }
    }
  const onFinish = (values: any) => {
    console.log('Success:', values);
    const pwd1=form.getFieldValue("password");
    const pwd2=form.getFieldValue("password2");
    if(pwd1.length<6){
        console.log("长度过短");
    }
    else if(pwd1!=pwd2){
        console.log("两次密码不相同");
    }
  };

  const onFill=()=>{
    form.setFieldsValue({username:"wikipali"});
    form.setFieldsValue({password:""});
    form.setFieldsValue({password2:""});
  }
  const onFinishFailed = (errorInfo: any) => {
    console.log('Failed:', errorInfo);
  };

  return (
    <Form
    form={form}
      name="control-hooks"
      labelCol={{ span: 8 }}
      wrapperCol={{ span: 16 }}
      initialValues={{ remember: true }}
      onFinish={onFinish}
      onFinishFailed={onFinishFailed}
    >
    <Form.Item
        label="note"
        name="note"
        rules={[{ required: true }]}
      >
        <Input />
      </Form.Item>

      <Form.Item
        label="用户名"
        name="username"
        rules={[{ required: true, message: 'Please input your username!' }]}
      >
        <Input onChange={onUsernameChange} />
      </Form.Item>

      <Form.Item
        label="密码"
        name="password"
        rules={[{ required: true, message: 'Please input your password!' }]}
      >
        <Input.Password />
      </Form.Item>
      <Form.Item
        label="重复密码"
        name="password2"
        rules={[{ required: true, message: 'Please input your password!' }]}
      >
        <Input.Password />
      </Form.Item>

<Form.Item
name="gender" lable="性别" rules={[{reqired:true}]}>
    <Select
    placeholder="Select a option and change input text above"
    onChange={onGerderChange}
    allowClear
    >
        <Option value="male">男</Option>
        <Option value="femail">女</Option>
        <Option value="other">其他</Option>
    </Select>
</Form.Item>
      <Form.Item wrapperCol={{ offset: 8, span: 16 }}>
        <Button type="primary" htmlType="submit">
          提交
        </Button>
        <Button type="link" htmlType="button" onClick={onFill}>填充</Button>
      </Form.Item>
    </Form>
  );
};