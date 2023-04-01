import { Link, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { List, Breadcrumb, Card, Row, Col } from "antd";
import { HomeOutlined } from "@ant-design/icons";

import { PaliToEn } from "../../utils";
import { get } from "../../request";
import { IPaliBookListResponse } from "../api/Corpus";
import TocStyleSelect from "./TocStyleSelect";
import FullSearchInput from "../fts/FullSearchInput";

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
}
const Widget = ({ root, path, tags, onChange }: IWidgetBookTreeList) => {
  console.log("path", path);
  let currRoot = root;
  const [tocData, setTocData] = useState<ITocTree[]>([]);
  const [currData, setCurrData] = useState<ITocTree[]>([]);
  const [bookPath, setBookPath] = useState<pathData[]>([]);
  const navigate = useNavigate();

  useEffect(() => {
    const newPath: pathData[] = path
      ? path.map((item) => {
          return { to: item, title: item };
        })
      : [];
    setBookPath(newPath);
    //TODO 找到路径
    const currPath = getListCurrRoot(tocData, newPath);
    console.log("curr path", currPath);
    setCurrData(currPath);
  }, [path]);

  useEffect(() => {
    if (root) {
      fetchBookTree(root);
    }
  }, [root]);

  function getListCurrRoot(
    allTocData: ITocTree[],
    currPath: pathData[]
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
        if (itPath.to === itAll.dir) {
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
  function fetchBookTree(category: string) {
    function treeMap(params: IPaliBookListResponse): ITocTree {
      return {
        title: params.name,
        dir: PaliToEn(params.name),
        key: params.tag.join(),
        tag: params.tag,
        children: Array.isArray(params.children)
          ? params.children.map(treeMap)
          : [],
      };
    }

    get<IPaliBookListResponse[]>(`/v2/palibook/${category}`).then((json) => {
      console.log("ajax", json);
      const treeData = json.map(treeMap);
      setTocData(treeData);
      const currPath = getListCurrRoot(treeData, bookPath);
      console.log("curr path", currPath);
      setCurrData(currPath);
    });
  }

  function pushDir(dir: string, title: string, tag: string[]): void {
    const newPath: string =
      bookPath.length > 0 ? bookPath.slice(-1)[0].to + "-" + dir : dir;
    bookPath.push({ to: newPath, title: title });
    setBookPath(bookPath);
    if (typeof onChange !== "undefined") {
      onChange({
        path: newPath.split("-"),
        tag: tag,
      });
    }
  }
  const handleChange = (value: string) => {
    console.log(`selected ${value}`);
    fetchBookTree(value);
    currRoot = value;
    setBookPath([]);
  };
  // TODO
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
                    {item.title}
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
          <TocStyleSelect style={root} onChange={handleChange} />
        </Col>
      </Row>
      <Card>
        <List
          dataSource={currData}
          renderItem={(item) => (
            <List.Item
              onClick={() => {
                console.log("click", item.title);
                setCurrData(item.children);
                pushDir(item.dir, item.title, item.tag);
              }}
            >
              {item.title}
            </List.Item>
          )}
        />
      </Card>
    </>
  );
};

export default Widget;
