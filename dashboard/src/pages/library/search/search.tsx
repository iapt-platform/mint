import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Row, Col, Breadcrumb, Space, Tabs, Select } from "antd";
import FullSearchInput from "../../../components/fts/FullSearchInput";
import BookTree from "../../../components/corpus/BookTree";
import FullTextSearchResult, {
  ISearchView,
} from "../../../components/fts/FullTextSearchResult";
import FtsBookList from "../../../components/fts/FtsBookList";
import FtsSetting from "../../../components/fts/FtsSetting";
import CaseList from "../../../components/dict/CaseList";
import PageNumberList from "../../../components/fts/PageNumberList";
import { Key } from "antd/es/table/interface";

const Widget = () => {
  const { key } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [bookRoot, setBookRoot] = useState("default");
  const [bookPath, setBookPath] = useState<string[]>([]);
  const navigate = useNavigate();
  const [pageType, setPageType] = useState("P");
  const [view, setView] = useState<ISearchView | undefined>("pali");
  const [caseWord, setCaseWord] = useState<string[]>();

  useEffect(() => {
    const v = searchParams.get("view");
    if (typeof v === "string") {
      setView(v as ISearchView);
    }
  }, [key, searchParams]);

  useEffect(() => {
    let currRoot: string | null;
    currRoot = localStorage.getItem("pali_path_root");
    if (currRoot === null) {
      currRoot = "default";
    }
    setBookRoot(currRoot);
  }, []);
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="1440px">
          <Row>
            <Col xs={0} sm={6} md={5}>
              <BookTree
                multiSelect={false}
                root={bookRoot}
                path={bookPath}
                onChange={(key: string[], path: string[]) => {
                  console.log("key", key);
                  if (key.length === 0) {
                    searchParams.delete("tags");
                  } else {
                    searchParams.set("tags", key.join(";"));
                  }
                  searchParams.delete("book");
                  setSearchParams(searchParams);
                  setBookPath(path);
                }}
              />
            </Col>
            <Col xs={24} sm={18} md={13}>
              <Space direction="vertical" style={{ padding: 10 }}>
                <Space>
                  <FullSearchInput
                    size="large"
                    width={"500px"}
                    value={key}
                    view={view}
                    tags={searchParams.get("tags")?.split(",")}
                    onSearch={(value: string) => {
                      navigate(`/search/key/${value}`);
                    }}
                    onPageTypeChange={(value: string) => {
                      setPageType(value);
                    }}
                  />
                  <FtsSetting
                    trigger="高级"
                    orderBy={searchParams.get("orderby")}
                    match={searchParams.get("match")}
                    onChange={(
                      key: string,
                      value: string | number | boolean
                    ) => {
                      searchParams.set(key, value.toString());
                      setSearchParams(searchParams);
                    }}
                  />
                </Space>
                <Breadcrumb>
                  {bookPath.map((item, id) => (
                    <Breadcrumb.Item key={id}>{item}</Breadcrumb.Item>
                  ))}
                </Breadcrumb>
                <Tabs
                  activeKey={view}
                  onChange={(activeKey: string) => {
                    setView(activeKey as ISearchView);
                    searchParams.set("view", activeKey);
                    setSearchParams(searchParams);
                  }}
                  size="small"
                  tabBarExtraContent={
                    <Space>
                      <Select
                        defaultValue="case"
                        bordered={false}
                        options={[
                          { value: "case", label: "变格查询" },
                          { value: "complete", label: "精确匹配" },
                          { value: "unaccented", label: "无变音符号查询" },
                        ]}
                        onSelect={(value: string) => {
                          searchParams.set("match", value);
                          setSearchParams(searchParams);
                        }}
                      />
                      <Select
                        defaultValue="rank"
                        bordered={false}
                        options={[
                          { value: "rank", label: "相关度降序" },
                          { value: "paragraph", label: "段落编号升序" },
                        ]}
                        onSelect={(value: string) => {
                          searchParams.set("orderby", value);
                          setSearchParams(searchParams);
                        }}
                      />
                      <Select
                        defaultValue="default"
                        bordered={false}
                        options={[
                          { value: "default", label: "全部单词" },
                          { value: "on", label: "黑体" },
                          { value: "off", label: "非黑体" },
                        ]}
                        onSelect={(value: string) => {
                          searchParams.set("bold", value);
                          setSearchParams(searchParams);
                        }}
                      />
                    </Space>
                  }
                  items={[
                    {
                      label: `巴利原文`,
                      key: "pali",
                      children: <></>,
                    },
                    {
                      label: `标题`,
                      key: "title",
                      children: <div></div>,
                    },
                    {
                      label: `页码`,
                      key: "page",
                      children: <></>,
                    },
                  ]}
                />
                <FullTextSearchResult
                  view={view as ISearchView}
                  pageType={pageType}
                  keyWord={key}
                  keyWords={caseWord}
                  tags={searchParams.get("tags")?.split(",")}
                  bookId={searchParams.get("book")}
                  orderBy={searchParams.get("orderby")}
                  match={searchParams.get("match")}
                  bold={searchParams.get("bold")}
                />
              </Space>
            </Col>
            <Col xs={0} sm={0} md={6}>
              {key && parseInt(key) ? (
                <PageNumberList
                  keyWord={key}
                  onSelect={(selectedKeys: Key[]) => {
                    console.log("selectedKeys", selectedKeys);
                    if (selectedKeys.length > 0) {
                      if (typeof selectedKeys[0] === "string") {
                        const queryString = selectedKeys[0].split("-");
                        if (queryString.length === 3) {
                          setCaseWord(queryString[1].split(","));
                          if (parseInt(queryString[2]) === 0) {
                            searchParams.delete("book");
                          } else {
                            searchParams.set("book", queryString[2]);
                          }
                          setSearchParams(searchParams);
                        }
                      }
                    }
                  }}
                />
              ) : (
                <CaseList
                  word={key}
                  lines={5}
                  onChange={(value: string[]) => setCaseWord(value)}
                />
              )}

              <FtsBookList
                view={view}
                keyWord={key}
                keyWords={caseWord}
                tags={searchParams.get("tags")?.split(",")}
                match={searchParams.get("match")}
                bookId={searchParams.get("book")}
                onSelect={(bookId: number) => {
                  if (bookId !== 0) {
                    searchParams.set("book", bookId.toString());
                  } else {
                    searchParams.delete("book");
                  }
                  setSearchParams(searchParams);
                }}
              />
            </Col>
          </Row>
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </>
  );
};

export default Widget;
