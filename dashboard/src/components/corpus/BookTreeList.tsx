import { Link, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { List, Breadcrumb, Card, Row, Col } from "antd";
import { HomeOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { IPaliBookListResponse } from "../api/Corpus";
import TocStyleSelect from "./TocStyleSelect";
import FullSearchInput from "../fts/FullSearchInput";
import PaliText from "../template/Wbw/PaliText";

export interface IEventBookTreeOnchange {
  path: string[];
  tag: string[];
}
export interface ITocTree {
  title: string;
  dir: string;
  key: string;
  tag: string[];
  path?: string[];
  children: ITocTree[];
}
interface pathData {
  to: string;
  title: string;
}
interface IWidgetBookTreeList {
  root?: string;
  path?: string[];
  tags?: string[];
  onChange?: Function;
  onTocLoad?: Function;
}
const BoolTreeListWidget = ({
  root = "default",
  path,
  tags,
  onChange,
  onTocLoad,
}: IWidgetBookTreeList) => {
  const [tocData, setTocData] = useState<ITocTree[]>([]);
  const [currData, setCurrData] = useState<ITocTree[]>([]);
  const [bookPath, setBookPath] = useState<pathData[]>([]);
  const [currRoot, setCurrRoot] = useState<string>();
  const navigate = useNavigate();

  useEffect(() => {
    setCurrRoot(root);
  }, [root]);

  useEffect(() => {
    let mPath: string[] = [];
    const newPath: pathData[] = path
      ? path.map((item) => {
          mPath.push(item);
          return { to: mPath.join("_"), title: item };
        })
      : [];

    setBookPath(newPath);
    const currDir = getListCurrRoot(tocData, mPath);
    console.log("currDir", currDir);
    setCurrData(currDir);
  }, [path, tocData]);

  useEffect(() => {
    function treeMap(params: IPaliBookListResponse): ITocTree {
      return {
        title: params.name,
        dir: params.name.toLowerCase(),
        key: params.tag.join(),
        tag: params.tag,
        children: Array.isArray(params.children)
          ? params.children.map(treeMap)
          : [],
      };
    }
    if (currRoot) {
      get<IPaliBookListResponse[]>(`/v2/palibook/${currRoot}`).then((json) => {
        console.log("Book List ajax", json);
        const treeData = json.map(treeMap);
        setTocData(treeData);
        if (typeof onTocLoad !== "undefined") {
          onTocLoad(json);
        }
      });
    }
  }, [currRoot]);

  useEffect(() => {
    const currPath =
      bookPath.length > 0 ? bookPath[bookPath.length - 1].to.split("_") : [];
    const currDir = getListCurrRoot(tocData, currPath);
    setCurrData(currDir);
  }, [bookPath, tocData]);

  function getListCurrRoot(
    allTocData: ITocTree[],
    currPath: string[]
  ): ITocTree[] {
    let curr: ITocTree[];
    if (allTocData.length > 0) {
      curr = allTocData;
    } else {
      return [];
    }

    for (const itPath of currPath) {
      let isFound = false;
      for (const itAll of curr) {
        if (itPath === itAll.dir) {
          curr = itAll.children;
          isFound = true;
          break;
        }
      }
      if (!isFound) {
        return [];
      }
    }
    return curr;
  }

  function pushDir(dir: string, title: string, tag: string[]): void {
    console.log("push dir", dir, title);

    const newPath: string = [...bookPath.map((item) => item.title), dir].join(
      "_"
    );
    bookPath.push({ to: newPath, title: title });
    console.log("newPath", newPath);
    console.log("book Path", bookPath);

    setBookPath(bookPath);
    if (typeof onChange !== "undefined") {
      onChange({
        path: newPath.split("_"),
        tag: tag,
      });
    }
  }

  return (
    <>
      <Row style={{ padding: 10 }}>
        <Col
          xs={18}
          sm={24}
          style={{ display: "flex", justifyContent: "space-between" }}
        >
          <Breadcrumb>
            <Breadcrumb.Item>
              <Link to={`/palicanon/list/${currRoot}`}>
                <HomeOutlined />
              </Link>
            </Breadcrumb.Item>
            {bookPath.map((item, id) => {
              return (
                <Breadcrumb.Item key={id}>
                  <Link to={`/palicanon/list/${currRoot}/${item.to}`}>
                    <PaliText text={item.title} />
                  </Link>
                </Breadcrumb.Item>
              );
            })}
          </Breadcrumb>
          <FullSearchInput
            tags={tags}
            onSearch={(value: string) => {
              navigate(`/search/key/${value}?tags=${tags}`);
            }}
          />
        </Col>
        <Col xs={6} sm={0} style={{ textAlign: "right" }}>
          <TocStyleSelect
            style={currRoot}
            onChange={(value: string) => {
              setCurrRoot(value);
            }}
          />
        </Col>
      </Row>
      <Card style={{ display: currData.length === 0 ? "none" : "block" }}>
        <List
          dataSource={currData}
          renderItem={(item) => (
            <List.Item
              onClick={() => {
                setCurrData(item.children);
                pushDir(
                  item.title.toLowerCase(),
                  item.title.toLowerCase(),
                  item.tag
                );
              }}
            >
              <PaliText text={item.title} />
            </List.Item>
          )}
        />
      </Card>
    </>
  );
};

export default BoolTreeListWidget;
