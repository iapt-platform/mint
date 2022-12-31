//主讲人列表
import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
//import { message } from "antd";

import { post } from "../../../request";
import { useState } from "react";
//import React from 'react';
import { Card, List , Col, Row , Space} from 'antd';
const { Meta } = Card;
//const {  Card, Col, Row  } = antd;

const data = [
  {
    title: 'U Kuṇḍadhāna Sayadaw',
    introduction: 'U Kuṇḍadhāna Sayadaw简介 U Kun西亚多今年51岁，30个瓦萨， - 1969年，出生于缅甸...',
    portrait:'https://img2.baidu.com/it/u=2930319359,2500787374&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=334'

  },
  {
    title: '某尊者',
    introduction: '某尊者简介...',
    portrait:'https://img2.baidu.com/it/u=2930319359,2500787374&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=334'
  },
  {
    title: '小僧善巧',
    introduction: '小僧善巧尊者简介...',
    portrait:'https://avatars.githubusercontent.com/u/58804044?v=4'
  },
  {
    title: 'Kalyāṇamitta',
    introduction: 'Kalyāṇamitta尊者简介...',
    portrait:'https://img2.baidu.com/it/u=2930319359,2500787374&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=334'
  },
];
/*栅格卡片实现方案 https://ant.design/components/card-cn/

const App = () => (
  <div className="site-card-wrapper">
    <Row gutter={16}>
      <Col span={4}>
      <Card
    hoverable
    style={{ width: 240,  height: 300}}
    cover={<img alt="example" src={data[0].portrait}  width="240" height="180"/>}
  >
    <Meta title={data[0].title} description={data[0].introduction} />
  </Card>
      </Col>
      <Col span={4}>
      <Card
    hoverable
    style={{ width: 240,  height: 300}}
    cover={<img alt="example" src={data[1].portrait}  width="240" height="180"/>}
  >
    <Meta title={data[1].title} description={data[1].introduction} />
  </Card>
      </Col>
      <Col span={4}>
      <Card
    hoverable
    style={{ width: 240,  height: 300}}
    cover={<img alt="example" src={data[2].portrait}  width="240" height="180"/>}
  >
    <Meta title={data[2].title} description={data[2].introduction} />
  </Card>
      </Col>
      <Col span={4}>
      <Card
    hoverable
    style={{ width: 240,  height: 300}}
    cover={<img alt="example" src={data[3].portrait}  width="240" height="180"/>}
  >
    <Meta title={data[3].title} description={data[3].introduction} />
  </Card>
      </Col>
    </Row>
  </div>
);

export default App;
*/


/*List实现方案

import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
//import { message } from "antd";

import { post } from "../../../request";
import { useState } from "react";
//import React from 'react';
import { Card, List } from 'antd';
const { Meta } = Card;
*/
const App: React.FC = () => (

  <List
    grid={{ gutter: 16, column: 4 }}
    dataSource={data}
    renderItem={(item) => (
      <List.Item>
        <Card
          hoverable
          style={{ width: 240,  height: 300}}
          cover={<img alt="example" src={item.portrait}  width="240" height="180"/>}
          >
          <Meta title={item.title} description={item.introduction} />
        </Card>
      </List.Item>
    )}
  />
      
);
export default App;
