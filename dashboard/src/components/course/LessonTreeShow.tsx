//上传封面组件
import React, { useState } from "react";
import { LoadingOutlined, PlusOutlined } from "@ant-design/icons";
import { message, Upload, Tree } from "antd";
import type { UploadChangeParam } from "antd/es/upload";
import type { RcFile, UploadFile, UploadProps } from "antd/es/upload/interface";

import type { DataNode } from "antd/es/tree";
/*
const dig = (path = "0", level = 3) => {
  const list = [];
  for (let i = 0; i < 10; i += 1) {
    const key = `a-${i}`;
    const treeNode: DataNode = {
      title: key,
      key,
    };

    if (level > 0) {
      treeNode.children = dig(key, level - 1);
    }

    list.push(treeNode);
  }
  return list;
};

const treeData = dig();
*/
const treeData: DataNode[] = [
  {
    title: "课程1",
    key: "0-0",
    children: [
      { title: "课程1-0", key: "0-0-0", isLeaf: true },
      { title: "课程1-1", key: "0-0-1", isLeaf: true },
      { title: "课程1-2", key: "0-0-2", isLeaf: true },
      { title: "课程1-3", key: "0-0-3", isLeaf: true },
    ],
  },
  {
    title: "课程2",
    key: "0-1",
    children: [
      { title: "课程2-0", key: "0-1-0", isLeaf: true },
      { title: "课程2-1", key: "0-1-1", isLeaf: true },
      { title: "课程2-2", key: "0-1-2", isLeaf: true },
    ],
  },
];
const Widget = () => {
  return <Tree treeData={treeData} height={233} defaultExpandAll />;
};

export default Widget;
