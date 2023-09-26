import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Row, Col, Breadcrumb, Space, Tabs } from "antd";
import FullSearchInput from "../../../components/fts/FullSearchInput";
import BookTree from "../../../components/corpus/BookTree";
import FullTextSearchResult, {
  ISearchView,
} from "../../../components/fts/FullTextSearchResult";
import FtsBookList from "../../../components/fts/FtsBookList";
import FtsSetting from "../../../components/fts/FtsSetting";
import CaseList from "../../../components/dict/CaseList";

const Widget = () => {
  const { key } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [bookRoot, setBookRoot] = useState("default");
  const [bookPath, setBookPath] = useState<string[]>([]);
  const [searchPage, setSearchPage] = useState(false);
  const navigate = useNavigate();
  const [pageType, setPageType] = useState("P");
  const [view, setView] = useState("pali");

  useEffect(() => {}, [key, searchParams]);

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
            <Col xs={24} sm={18} md={14}>
              <Space direction="vertical" style={{ padding: 10 }}>
                <Space>
                  <FullSearchInput
                    size="large"
                    width={"500px"}
                    value={key}
                    searchPage={searchPage}
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
                  onChange={(activeKey: string) => {
                    setView(activeKey);
                    searchParams.set(view, activeKey);
                    setSearchParams(searchParams);
                    switch (activeKey) {
                      case "pali":
                        setSearchPage(false);
                        break;
                      case "page":
                        setSearchPage(true);
                        break;
                      default:
                        break;
                    }
                  }}
                  size="small"
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
                  tags={searchParams.get("tags")?.split(",")}
                  bookId={searchParams.get("book")}
                  orderBy={searchParams.get("orderby")}
                  match={searchParams.get("match")}
                />
              </Space>
            </Col>
            <Col xs={0} sm={0} md={5}>
              <CaseList word={key} lines={5} />
              <FtsBookList
                view={view}
                keyWord={key}
                tags={searchParams.get("tags")?.split(",")}
                match={searchParams.get("match")}
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
