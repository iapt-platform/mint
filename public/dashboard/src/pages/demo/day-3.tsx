import React,{useState} from 'react';
import { Table,Form, Input, Button, Select,Radio,Switch,DatePicker,Popconfirm,message,Space,Checkbox } from 'antd';
import { CloseOutlined,CheckOutlined } from '@ant-design/icons';

const {Option}=Select;



export default () => {
    const [form] = Form.useForm();

    const onUsernameChange=(value:any)=>{
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
  //表单完成
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

  //填充表单
  const onFill=()=>{
    form.setFieldsValue({username:"wikipali"});
    form.setFieldsValue({password:""});
    form.setFieldsValue({password2:""});
  }
  //失败
  const onFinishFailed = (errorInfo: any) => {
    console.log('Failed:', errorInfo);
  };
  //提交表单
  const onSubmit=()=>{
    console.log("form:",form);
    getTableData(form.getFieldValue("username"));

  }
  const validatePassword=(rule,value,callback)=>{
    console.log(value);
    const pwd1=form.getFieldValue("password");
    if(value && value!==pwd1){
      callback("两次输入不一致");
    }
    else{
      callback();
    }
  }

  const [valueLang,setValueLang] = React.useState(1);
  const onChangeLang = e =>{
    console.log('radio lang checked',e.target.value);
    setValueLang(e.target.value);

  }

  function onChangeDate(value,dateString){
    console.log("selectd time",value);
    console.log('formatted selected time:',dateString);
  }
  function onOkDate(value){
    console.log('onOk:',value);
  }
  function confirm(){
    message.info('clicked on yes.');
  }
  function onChangeCheckbox(e){
    console.log("checked:",e.target.checked);
  }
  //表头
const columns = [
	{
		title: 'Id',
		dataIndex: 'id',
		key: 'id',
		render: text => <a>{text}</a>,
	},
	{
		title: 'name',
		dataIndex: 'name',
		key: 'name',
	},
	{
		title: "gender",
		dataIndex: 'gender',
		key: 'gender',
	},
  {
		title: "email",
		dataIndex: 'email',
		key: 'email',
	}
]
function getTableData(name:string){
  fetch('https://gorest.co.in/public-api/users?name='+name)
    .then(function (response) {
      console.log("ajex:", response);
      return response.json();
    })
    .then(function (myJson) {
      console.log("ajex",myJson.data);
      setTableData(myJson.data);
    });		
}  
const [tableData, setTableData] = useState();
  return (
    <div>
      <Table dataSource={tableData} columns={columns} />
    <Form
    form={form}
      name="control-hooks"
      labelCol={{ span: 8 }}
      wrapperCol={{ span: 16 }}
      initialValues={{ remember: true }}
      onFinish={onFinish}
      onFinishFailed={onFinishFailed}
      onSubmitCapture={onSubmit}
    >
    <Form.Item
        label="note"
        name="note"
      >
        <Input />
      </Form.Item>

      <Form.Item
        label="用户名"
        name="username"
        initialValue="haha"
        rules={[{ required: true, message: "Please input your '${name}'!" }]}
      >
        <Input onChange={onUsernameChange} />
      </Form.Item>

      <Form.Item
        label="密码"
        name="password"
        rules={[
          { required: true, message: 'Please input your password!' },
          { min: 6, message: '不能少于六个字符' },
          { whitespace: false, message: '不能包含空格' }
        ]}
      >
        <Input.Password />
      </Form.Item>
      <Form.Item
        label="重复密码"
        name="password2"
        validateTrigger="onBlue"
        dependencies={['password']}
        rules={[
          { required: true, message: 'Please input your password!' },
           /*
          {
            validator:validatePassword
          }
         */
          
         ({getFieldValue})=>({
           validator(_,value){
             if(!value || getFieldValue('password')===value){
               return Promise.resolve();
             }
             return Promise.reject(new Error('两次密码不匹配'));
           }
         })
         
        ]}
      >


        <Input.Password />
      </Form.Item>

  <Form.Item name="gender" label="性别" >
      <Select
      placeholder="Select a option and change input text above"
      onChange={onGerderChange}
      allowClear
      >
          <Option value="male">男</Option>
          <Option value="female">女</Option>
          <Option value="other">其他</Option>
      </Select>
  </Form.Item>
  <Form.Item name="lang" label="界面语言">
    {
      
    }
    <Radio.Group onChange={onChangeLang} value={valueLang}>
        <Radio value={'en'}>英文</Radio>
        <Radio value={'zh-hans'}>简体中文</Radio>
        <Radio value={'zh-hant'}>繁体中文</Radio>
    </Radio.Group>
  </Form.Item>
  <Form.Item name="datetime" label="开始时间">
    <DatePicker showTime onChange={onChangeDate} onOk={onOkDate}/>
  </Form.Item>
  <Form.Item name="org_text" label="显示原文">
    <Switch checkedChildren={<CheckOutlined />} unCheckedChildren={<CloseOutlined />} defaultChecked />
  </Form.Item>
  <Form.Item name="checkbox" label="checkbox">
    <Checkbox onChange={onChangeCheckbox}>checkbox</Checkbox>
  </Form.Item>
      <Form.Item wrapperCol={{ offset: 8, span: 16 }}>
        <Button type="primary" htmlType="submit">
          提交
        </Button>
        <Popconfirm placement="top" title="清空吗？" onConfirm={confirm} okText="是" cancelText="否">
          <Button type="link" htmlType="button" onClick={onFill}>清空</Button>
        </Popconfirm>
        
      </Form.Item>
    </Form>

          
    </div>

  );
};