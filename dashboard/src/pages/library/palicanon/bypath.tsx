import { useNavigate } from "react-router-dom";
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Affix, Drawer, Row, Col } from "antd";

import BookTree from "../../../components/corpus/BookTree";
import BookTreeList from "../../../components/corpus/BookTreeList";
import type { IEventBookTreeOnchange } from "../../../components/corpus/BookTreeList";
import PaliChapterListByTag from "../../../components/corpus/PaliChapterListByTag";
import BookViewer from "../../../components/corpus/BookViewer";
import { IChapterClickEvent } from "../../../components/corpus/PaliChapterList";
import { IPaliBookListResponse } from "../../../components/api/Corpus";
import Recent from "../../../components/corpus/Recent";
import { fullUrl } from "../../../utils";

const Widget = () => {
  const { root, path } = useParams();
  const navigate = useNavigate();
  const defaultPath: string[] = path ? path.split("_") : [];
  const [bookRoot, setBookRoot] = useState(root);
  const [bookPath, setBookPath] = useState(defaultPath);
  const [bookTag, setBookTag] = useState<string[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [openPara, setOpenPara] = useState({ book: 0, para: 0 });
  const [drawerTitle, setDrawerTitle] = useState("");
  const [tocData, setTocData] = useState<IPaliBookListResponse[]>([]);

  // 根据路径，遍历目录树，获取标签
  const getTagByPath = (
    _path?: string,
    _tocData?: IPaliBookListResponse[]
  ): string[] => {
    if (typeof _path === "undefined" || _path === "") {
      return [];
    }
    if (typeof _tocData === "undefined" || _tocData.length === 0) {
      return [];
    }
    const arrPath = _path ? _path.split("_") : [];
    let currToc = _tocData;
    let nextToc: IPaliBookListResponse[] | undefined;
    let found = false;
    let tags: string[] = [];
    for (const itPath of arrPath) {
      for (const itToc of currToc) {
        if (itPath === itToc.name.toLowerCase()) {
          found = true;
          nextToc = itToc.children;
          tags = itToc.tag;
          break;
        }
      }
      if (found && nextToc) {
        currToc = nextToc;
      } else {
        break;
      }
    }
    return tags;
  };

  useEffect(() => {
    let currRoot: string | null;
    if (typeof root === "undefined") {
      currRoot = localStorage.getItem("pali_path_root");
      if (currRoot === null) {
        currRoot = "default";
      }
      navigate("/palicanon/list/" + currRoot);
    } else {
      currRoot = root;
    }
    const arrPath = path ? path.split("_") : [];
    setBookPath(arrPath);
    setBookRoot(currRoot);
    const currTags = getTagByPath(path, tocData);
    setBookTag(currTags);
    console.log("index-load", root, path, currTags);
  }, [root, path, navigate, tocData]);

  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="1260px">
          <Row>
            <Col xs={0} sm={6} md={6}>
              <Affix offsetTop={0}>
                <div style={{ height: "100vh", overflowY: "auto" }}>
                  <BookTree
                    multiSelectable={false}
                    root={bookRoot}
                    path={bookPath}
                    onRootChange={(root: string) =>
                      navigate("/palicanon/list/" + root)
                    }
                    onChange={(key: string[], path: string[]) => {
                      navigate(
                        `/palicanon/list/${bookRoot}/${path
                          .join("_")
                          .toLowerCase()}`
                      );
                      console.log("key", key);
                      if (key.length > 0) {
                        setBookTag(key[0].split(","));
                      }
                      setBookPath(path);
                    }}
                  />
                </div>
              </Affix>
            </Col>
            <Col xs={24} sm={18} md={13}>
              <BookTreeList
                tags={bookTag}
                root={bookRoot}
                path={bookPath}
                onChange={(e: IEventBookTreeOnchange) => {
                  console.log("book tree list on change", e);
                  navigate(`/palicanon/list/${bookRoot}/${e.path.join("_")}`);
                }}
                onTocLoad={(toc: IPaliBookListResponse[]) => {
                  setTocData(toc);
                }}
              />
              <PaliChapterListByTag
                tag={bookTag}
                onChapterClick={(e: IChapterClickEvent) => {
                  if (e.event.ctrlKey) {
                    const url = `/palicanon/chapter/${e.para.Book}-${e.para.Paragraph}`;
                    window.open(fullUrl(url), "_blank");
                  } else {
                    setIsModalOpen(true);
                    setOpenPara({ book: e.para.Book, para: e.para.Paragraph });
                    setDrawerTitle(e.para.Title);
                  }
                }}
              />
            </Col>
            <Col xs={0} sm={0} md={5}>
              <Recent />
            </Col>
          </Row>
        </Col>
        <Col flex="auto"></Col>
      </Row>

      <Drawer
        title={drawerTitle}
        placement="right"
        open={isModalOpen}
        onClose={() => {
          setIsModalOpen(false);
        }}
        size="large"
        style={{ minWidth: 736, maxWidth: "100%" }}
        contentWrapperStyle={{ overflowY: "auto" }}
        footer={null}
      >
        <div>
          <BookViewer chapter={openPara} />
        </div>
      </Drawer>
    </>
  );
};

export default Widget;
