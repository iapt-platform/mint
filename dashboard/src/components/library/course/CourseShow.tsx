//课程详情图片标题按钮主讲人组合
import { Link } from "react-router-dom";
import React from 'react';
import { ProForm, ProFormText } from "@ant-design/pro-components";
import {Layout,  Image, Button, Space , Col, Row } from 'antd';

const Widget = () => {
  

  return (
    <ProForm.Group>
        <Layout>
          <Row>
    <Image
    width={200}
    src="https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fimg95.699pic.com%2Fxsj%2F0g%2Fk2%2F7d.jpg%21%2Ffw%2F700%2Fwatermark%2Furl%2FL3hzai93YXRlcl9kZXRhaWwyLnBuZw%2Falign%2Fsoutheast&refer=http%3A%2F%2Fimg95.699pic.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1673839902&t=d8f4306ddd6935313c66efb936cbe268"
  />
    <h1 style={{ "fontWeight": 'bold', "fontSize": 30}}>wikipali课程</h1>

          <Col flex="auto"></Col>
          <Col flex="1260px">   </Col>
          </Row>
          <Col>
          <Button type="primary">关注</Button>
          </Col>




    <p style={{ "fontWeight": 'bold', "fontSize": 15}}>主讲人： <Link to="/course/lesson/12345">小僧善巧</Link> </p>
    
    </Layout>
      </ProForm.Group>
    );
};

export default Widget;


/*
<Button type="primary">关注</Button>
<Button type="primary" disabled>
  已关注
</Button>
*/