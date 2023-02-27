import { useNavigate } from "react-router-dom";
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Affix, Drawer, Row, Col } from "antd";
import { message } from "antd";

import BookTree from "../../../components/corpus/BookTree";
import BookTreeList from "../../../components/corpus/BookTreeList";
import type { IEventBookTreeOnchange } from "../../../components/corpus/BookTreeList";
import PaliChapterListByTag from "../../../components/corpus/PaliChapterListByTag";
import BookViewer from "../../../components/corpus/BookViewer";
import { IChapterClickEvent } from "../../../components/corpus/PaliChapterList";

const Widget = () => {
  const { root, path, tag } = useParams();
  const navigate = useNavigate();
  const defaultPath: string[] = path ? path.split("-") : [];
  const [bookRoot, setBookRoot] = useState(root);
  const [bookPath, setBookPath] = useState(defaultPath);
  const [bookTag, setBookTag] = useState([""]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [openPara, setOpenPara] = useState({ book: 0, para: 0 });
  const [drawerTitle, setDrawerTitle] = useState("");

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
    const arrPath = path ? path.split("-") : [];
    setBookPath(arrPath);
    setBookRoot(currRoot);
    console.log("index-load", root);
  }, [root, path]);

  // TODO
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
                    root={bookRoot}
                    path={bookPath}
                    onChange={(key: string, path: string[]) => {
                      navigate(
                        `/palicanon/list/${bookRoot}/${path
                          .join("-")
                          .toLowerCase()}`
                      );
                      console.log(key);
                      setBookTag(key.split(","));
                      setBookPath(path);
                    }}
                  />
                </div>
              </Affix>
            </Col>
            <Col xs={24} sm={18} md={14}>
              <BookTreeList
                root={bookRoot}
                path={bookPath}
                onChange={(e: IEventBookTreeOnchange) => {
                  navigate(`/palicanon/list/${bookRoot}/${e.path.join("-")}`);
                  message.info(e.tag.join());
                  setBookTag(e.tag);
                }}
              />
              <PaliChapterListByTag
                tag={bookTag}
                onChapterClick={(e: IChapterClickEvent) => {
                  if (e.event.ctrlKey) {
                    window.open(
                      `/my/palicanon/chapter/${e.para.Book}-${e.para.Paragraph}`,
                      "_blank"
                    );
                  } else {
                    setIsModalOpen(true);
                    setOpenPara({ book: e.para.Book, para: e.para.Paragraph });
                    setDrawerTitle(e.para.Title);
                  }
                }}
              />
            </Col>
            <Col xs={0} sm={0} md={4}>
              侧边栏 侧边栏 侧边栏 侧边栏 侧边栏
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
