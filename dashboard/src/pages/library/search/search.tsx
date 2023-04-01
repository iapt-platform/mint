import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Row, Col, Breadcrumb, Space } from "antd";
import FullSearchInput from "../../../components/fts/FullSearchInput";
import BookTree from "../../../components/corpus/BookTree";
import FullTextSearchResult from "../../../components/fts/FullTextSearchResult";
import FtsBookList from "../../../components/fts/FtsBookList";
import FtsSetting from "../../../components/fts/FtsSetting";

const Widget = () => {
  const { key } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [bookRoot, setBookRoot] = useState("default");
  const [bookPath, setBookPath] = useState<string[]>([]);
  const navigate = useNavigate();

  useEffect(() => {}, [key, searchParams]);

  useEffect(() => {
    let currRoot: string | null;
    currRoot = localStorage.getItem("pali_path_root");
    if (currRoot === null) {
      currRoot = "default";
    }
    setBookRoot(currRoot);
  }, []);
  // TODO
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="1440px">
          <Row>
            <Col xs={0} sm={6} md={5}>
              <BookTree
                root={bookRoot}
                path={bookPath}
                onChange={(key: string, path: string[]) => {
                  console.log("key", key);
                  if (key === "") {
                    searchParams.delete("tags");
                  } else {
                    searchParams.set("tags", key);
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
                    tags={searchParams.get("tags")?.split(",")}
                    onSearch={(value: string) => {
                      navigate(`/search/key/${value}`);
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
                <FullTextSearchResult
                  keyWord={key}
                  tags={searchParams.get("tags")?.split(",")}
                  bookId={searchParams.get("book")}
                  orderBy={searchParams.get("orderby")}
                  match={searchParams.get("match")}
                />
              </Space>
            </Col>
            <Col xs={0} sm={0} md={5}>
              <FtsBookList
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
