import { Link } from "react-router-dom";
import { useState, useEffect } from "react";
import { List, Breadcrumb, Card, Select, Space } from "antd";
import { PaliToEn } from "../../utils";
import { get } from "../../request";
import { IPaliBookListResponse } from "../api/Corpus";
const { Option } = Select;

interface IWidgetBookTreeList {
  root?: string;
  path?: string[];
  onChange?: Function;
}
export interface IEventBookTreeOnchange {
  path: string[];
  tag: string[];
}
const Widget = (prop: IWidgetBookTreeList) => {
  let treeData: NewTree[] = [];
  let currRoot = prop.root;
  const defuaultData: NewTree[] = [];
  const [currData, setCurrData] = useState(defuaultData);

  const defaultPath: pathData[] = prop.path
    ? prop.path.map((item) => {
        return { to: item, title: item };
      })
    : [];
  const [bookPath, setBookPath] = useState(defaultPath);

  useEffect(() => {
    if (prop.root) fetchBookTree(prop.root);
  }, [prop.root]);

  type OrgTree = {
    name: string;
    tag: string[];
    children: OrgTree[];
  };
  type NewTree = {
    title: string;
    dir: string;
    key: string;
    tag: string[];
    children: NewTree[];
  };

  function fetchBookTree(category: string) {
    function treeMap(params: IPaliBookListResponse): NewTree {
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
      treeData = json.map(treeMap);
      setCurrData(treeData);
    });
  }

  interface pathData {
    to: string;
    title: string;
  }

  function pushDir(dir: string, title: string, tag: string[]): void {
    const newPath: string =
      bookPath.length > 0 ? bookPath.slice(-1)[0].to + "-" + dir : dir;
    bookPath.push({ to: newPath, title: title });
    setBookPath(bookPath);
    if (prop.onChange) {
      prop.onChange({
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
      <Space>
        <Select
          style={{ width: 90 }}
          defaultValue={prop.root}
          loading={false}
          onChange={handleChange}
        >
          <Option value="defualt">Defualt</Option>
          <Option value="cscd">CSCD</Option>
        </Select>
        <Breadcrumb>
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
      </Space>
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
