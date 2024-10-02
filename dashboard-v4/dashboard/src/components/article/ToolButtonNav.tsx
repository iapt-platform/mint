import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Button, message, Space, Tag, Tree, Typography } from "antd";
import { CompassOutlined, ReloadOutlined } from "@ant-design/icons";

import ToolButton from "./ToolButton";
import { useAppSelector } from "../../hooks";
import { sentenceList } from "../../reducers/sentence";
import ToolButtonNavMore from "./ToolButtonNavMore";
import ToolButtonNavSliceTitle from "./ToolButtonNavSliceTitle";
import { fullUrl } from "../../utils";

const { Text } = Typography;

interface DataNode {
  title: React.ReactNode;
  key: string;
  isLeaf?: boolean;
  children?: DataNode[];
}
interface ISlice {
  id: number;
  para: string;
  len: number;
}
interface IWidget {
  type?: string;
  articleId?: string;
}
const ToolButtonNavWidget = ({ type, articleId }: IWidget) => {
  const [treeData, setTreeData] = useState<DataNode[]>([]);
  const [slice, setSlice] = useState<number>(1);
  const navigate = useNavigate();

  const allSentList = useAppSelector(sentenceList);

  const refresh = () => {
    const divList = document.querySelectorAll("div.pcd_sent");

    let sentList: string[] = [];
    for (let index = 0; index < divList.length; index++) {
      const element = divList[index];
      const id = element.id.split("_");
      sentList.push(id[1]);
    }

    //计算总字符数
    let allStrLen = 0;
    allSentList.forEach((value) => {
      allStrLen += value.origin ? value.origin[0].length : 0;
    });
    const oneSliceLen = allStrLen / slice;

    let paraSlice: ISlice[] = [];
    let currSliceId = 0;
    let currSliceLen = 0;
    for (let index = 0; index < sentList.length; index++) {
      const sent = sentList[index];
      const currPara = sent.split("-").slice(0, 2).join("-");
      if (!paraSlice.find((value) => value.para === currPara)) {
        let paraLen = 0; //段落字符长度
        allSentList
          .filter(
            (value) => value.id.split("-").slice(0, 2).join("-") === currPara
          )
          .map((item) => (item.origin ? item.origin[0] : ""))
          .forEach((value) => {
            paraLen += value.length;
          });

        //计算如果放进去或者不放进去哪个更接近块的预设大小
        let next = currSliceId;
        if (currSliceLen + paraLen <= oneSliceLen) {
          //放进去还不到一个块，直接放进去
          currSliceLen = currSliceLen + paraLen;
        } else if (currSliceLen === 0) {
          //当前块里没东西直接放进去
          if (paraLen >= oneSliceLen) {
            //此块比一个块大
            next = currSliceId + 1;
            currSliceLen = 0;
          } else {
            currSliceLen = paraLen;
          }
        } else {
          //放进去超过一个块，需要比较是放进去好还是不放进去好
          const remain = oneSliceLen - currSliceLen;
          const extra = currSliceLen + paraLen - oneSliceLen;
          if (remain < extra) {
            //移到下一个块
            currSliceId++;
            next = currSliceId;
            currSliceLen = paraLen;
          } else {
            //放这个块
            currSliceLen += paraLen;
          }
        }
        paraSlice.push({ id: currSliceId, para: currPara, len: paraLen });
        currSliceId = next;
      }
    }

    const mSlice: DataNode[] = new Array(currSliceId + 1)
      .fill(1)
      .map((item, index) => {
        let sliceStrLen = 0;
        let sliceChildren: string[] = [];
        const newTree: DataNode[] = paraSlice
          .filter((value) => value.id === index)
          .map((item) => {
            const children = sentList
              .filter(
                (value) => value.split("-").slice(0, 2).join("-") === item.para
              )
              .map((item1) => {
                const str = allSentList.find((value) => value.id === item1);
                return {
                  title: str
                    ? str.origin
                      ? str.origin[0].slice(0, 30)
                      : item1
                    : item1,
                  key: item1,
                };
              });
            sliceStrLen += item.len;
            sliceChildren.push(item.para);
            return {
              title: item.para + "-" + item.len.toString(),
              key: item.para,
              children: children,
            };
          });

        return {
          title: (
            <ToolButtonNavSliceTitle
              label={
                <Space>
                  <Text>{`第${index + 1}组`}</Text>
                  <Tag>{`${sliceStrLen}`}</Tag>
                </Space>
              }
              onMenuClick={(key: string) => {
                if (sliceChildren.length > 0) {
                  const [book, para] = sliceChildren[0].split("-");
                  const paraList = sliceChildren.map(
                    (item) => item.split("-")[1]
                  );
                  let url = `/article/para/${book}?par=${paraList.join(",")}`;
                  console.log("url", url);

                  switch (key) {
                    case "copy-link":
                      navigator.clipboard.writeText(fullUrl(url)).then(() => {
                        message.success("链接地址已经拷贝到剪贴板");
                      });

                      break;
                    case "open":
                      navigate(url);
                      break;
                  }
                }
              }}
            />
          ),
          key: `slice_${index}`,
          children: newTree,
        };
      });
    if (mSlice.length > 1) {
      setTreeData(mSlice);
    } else if (mSlice.length === 1) {
      setTreeData(mSlice[0].children ? mSlice[0].children : []);
    }
  };

  useEffect(refresh, [slice]);
  return (
    <ToolButton
      title="导航"
      icon={<CompassOutlined />}
      content={
        <>
          <div style={{ textAlign: "right" }}>
            <Space>
              <Button
                onClick={() => {
                  refresh();
                }}
                size="small"
                type="link"
                icon={<ReloadOutlined />}
              />
              <ToolButtonNavMore
                onSliceChange={(value: number) => {
                  console.log(`selected ${value}`);
                  setSlice(value);
                }}
              />
            </Space>
          </div>

          <Tree
            treeData={treeData}
            titleRender={(node) => {
              const ele = document.getElementById(node.key);
              return (
                <div
                  onClick={() => {
                    ele?.scrollIntoView();
                  }}
                >
                  {node.title}
                </div>
              );
            }}
          />
        </>
      }
    />
  );
};

export default ToolButtonNavWidget;
