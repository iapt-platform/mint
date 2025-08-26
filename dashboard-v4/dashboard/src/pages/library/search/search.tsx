import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Row, Col, Breadcrumb, Space, Tabs, Select, Button, Affix } from "antd";
import FullSearchInput from "../../../components/fts/FullSearchInput";
import FullTextSearchResult, {
  IFtsItem,
  IFtsResponse,
  ISearchView,
} from "../../../components/fts/FullTextSearchResult";
import FtsBookList from "../../../components/fts/FtsBookList";
import FtsSetting from "../../../components/fts/FtsSetting";
import CaseList from "../../../components/dict/CaseList";
import PageNumberList from "../../../components/fts/PageNumberList";
import { Key } from "antd/es/table/interface";

import BookTreeWithTags from "../../../components/corpus/BookTreeWithTags";
import AIChatComponent from "../../../components/chat/AiChat";
import { get } from "../../../request";

const Widget = () => {
  const { key } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [bookPath, setBookPath] = useState<string[]>([]);
  const navigate = useNavigate();
  const [pageType, setPageType] = useState("P");
  const [view, setView] = useState<ISearchView | undefined>("pali");
  const [caseWord, setCaseWord] = useState<string[]>();
  const [sysPrompt, setSysPrompt] = useState<string>();

  const [ftsData, setFtsData] = useState<IFtsItem[]>();
  const [total, setTotal] = useState<number>();
  const [loading, setLoading] = useState(false);
  const [currPage, setCurrPage] = useState<number>(1);

  const [chat, setChat] = useState(false);

  useEffect(() => {
    const v = searchParams.get("view");
    if (typeof v === "string") {
      setView(v as ISearchView);
    }
  }, [key, searchParams]);

  const sTags = searchParams.get("tags")?.split(",");
  const bookId = searchParams.get("book");
  const orderBy = searchParams.get("orderby");
  const match = searchParams.get("match");
  const bold = searchParams.get("bold");

  useEffect(
    () => setCurrPage(1),
    [view, key, caseWord, sTags, bookId, match, pageType, bold]
  );

  useEffect(() => {
    /**
     * 搜索引擎选择逻辑
     * 如果 keyWord 包涵空格 使用 tulip
     * 如果 keyWord 不包涵空格 使用 wbw
     */
    let words;
    let api = "";
    if (key?.trim().includes(" ")) {
      api = "search";
      words = key;
    } else {
      api = "search-pali-wbw";
      words = caseWord?.join();
    }

    let url = `/v2/${api}?view=${view}&key=${words}`;
    if (typeof sTags !== "undefined") {
      url += `&tags=${sTags}`;
    }
    if (bookId) {
      url += `&book=${bookId}`;
    }
    if (orderBy) {
      url += `&orderby=${orderBy}`;
    }
    if (match) {
      url += `&match=${match}`;
    }
    if (pageType) {
      url += `&type=${pageType}`;
    }
    if (bold) {
      url += `&bold=${bold}`;
    }
    const offset = (currPage - 1) * 10;
    url += `&limit=10&offset=${offset}`;
    console.log("fetch url", url);
    setLoading(true);
    get<IFtsResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("data", json.data);
          const result: IFtsItem[] = json.data.rows.map((item) => {
            return {
              book: item.book,
              paragraph: item.paragraph,
              title: item.title ? item.title : item.paliTitle,
              paliTitle: item.paliTitle,
              content: item.highlight
                ? item.highlight.replaceAll("** ti ", "**ti ")
                : item.content,
              path: item.path,
              rank: item.rank,
            };
          });
          setFtsData(result);
          setTotal(json.data.count);
          if (result && result.length > 0) {
            const chat = result
              .map((item) => {
                return `## ${item.title}-${item.paliTitle} \n\n${item.content}\n\n`;
              })
              .join("");
            setSysPrompt(
              `# 搜索词：${key}\n\n# 搜索结果：\n\n${chat}\n\n请根据上述巴利文本内容,回答用户的问题。并猜测用户可能提问的下一个问题。列在每次回答的结尾处。可能的问题包括但是不限于：1. 生成一个概要的分类 2. 生成百科词条 范例：\n\n**下一个问题**\n\n 1. 问题1`
            );
          }
        } else {
          console.error(json.message);
        }
      })
      .finally(() => setLoading(false));
  }, [
    bold,
    bookId,
    caseWord,
    currPage,
    key,
    match,
    orderBy,
    pageType,
    sTags,
    view,
  ]);

  let bookRoot = "default";
  const currRoot = localStorage.getItem("pali_path_root");
  if (currRoot) {
    bookRoot = currRoot;
  }

  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col>
          <Row>
            <Col xs={0} sm={6} md={5}>
              <BookTreeWithTags
                view={view}
                keyWord={key}
                keyWords={caseWord}
                multiSelect={false}
                root={bookRoot}
                path={bookPath}
                onChange={(key: string[], path: string[]) => {
                  console.debug("key", key);
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
            <Col xs={24} sm={18} md={12}>
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
                <AIChatComponent
                  systemPrompt={sysPrompt}
                  onChat={() => setChat(true)}
                />

                {chat ? (
                  <></>
                ) : (
                  <FullTextSearchResult
                    view={view}
                    ftsData={ftsData}
                    total={total}
                    loading={loading}
                    currPage={currPage}
                    onChange={(page) => {
                      console.log(page);
                      setCurrPage(page);
                    }}
                  />
                )}
              </Space>
            </Col>
            <Col xs={0} sm={0} md={7}>
              <Affix offsetTop={0}>
                <div style={{ height: "100vh", overflowY: "auto" }}>
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
                  {chat ? (
                    <FullTextSearchResult
                      view={view}
                      ftsData={ftsData}
                      total={total}
                      loading={loading}
                      currPage={currPage}
                      onChange={(page) => {
                        console.log(page);
                        setCurrPage(page);
                      }}
                    />
                  ) : (
                    <></>
                  )}
                </div>
              </Affix>
            </Col>
          </Row>
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </>
  );
};

export default Widget;
