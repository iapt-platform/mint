//课程详情简介
import { Link } from "react-router-dom";
import React from 'react';
import { ProForm, ProFormText } from "@ant-design/pro-components";
import {Layout,  Descriptions, Space , Col, Row } from 'antd';

import ReactPlayer from 'react-player'

const Widget = () => {
  

  return (
    <ProForm.Group>
        <Layout>
        <Descriptions title="课程简介">
    <Descriptions.Item label=" ">每一尊佛体证后，都会有一次殊胜的大聚会，那是十方天人的相聚，由此宣说《大集会经》。
佛陀观察到：天人们内心有种种问题，他们却不知该如何表达……
于是便有了化身佛在问，本尊佛在答……
 
根据众生的根性，佛陀共开示六部经，分别针对贪、瞋、痴、信、觉、寻六种性格习性的天人。
此部《纷争分歧经》便是专门为瞋行者量身而作，瞋行者往往多思、多慧，佛陀便以智慧循循善诱，抽丝剥茧，层层深入，探究纷争、分歧等八种烦恼根源何在……
 
听，佛陀在说——
让我们以佛陀当年的语言——古老的巴利语——去聆听佛陀的教诲……</Descriptions.Item>
  </Descriptions>
  <Descriptions title="电子平台课堂笔记">
    <Descriptions.Item label="快速预览（课前预习）">
      <Link to="/course/lesson/12345">原文</Link>  <Link to="/course/lesson/23456">原文+义注</Link> </Descriptions.Item>
  </Descriptions>
  <ReactPlayer
            className='react-player fixed-bottom'
            url= 'https://assets-hk.wikipali.org/video/admissions1080p.mp4'
            width='50%'
            height='50%'
            controls = {true}

          />
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