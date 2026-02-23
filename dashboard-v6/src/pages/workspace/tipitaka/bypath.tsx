import { useNavigate, useParams } from "react-router";
import { useEffect, useMemo, useState } from "react";
import { Affix, Drawer, Row, Col } from "antd";

import type { IPaliBookListResponse } from "../../../api/Corpus";
import BookTree from "../../../components/tipitaka/BookTree";
import BookTreeList, {
  type IEventBookTreeOnchange,
} from "../../../components/tipitaka/BookTreeList";
import PaliChapterListByTag from "../../../components/tipitaka/PaliChapterListByTag";
import type { IChapterClickEvent } from "../../../components/tipitaka/PaliChapterList";
import BookViewer from "../../../components/tipitaka/BookViewer";
import Recent from "../../../components/recent/Recent";

// 将纯逻辑函数移出组件外，避免每次渲染都重新定义
const getTagByPath = (
  _path?: string,
  _tocData?: IPaliBookListResponse[]
): string[] => {
  if (!_path || !_tocData || _tocData.length === 0) return [];

  const arrPath = _path.split("_");
  let currToc = _tocData;
  let tags: string[] = [];

  for (const itPath of arrPath) {
    const foundNode = currToc.find((it) => it.name.toLowerCase() === itPath);
    if (foundNode) {
      tags = foundNode.tag;
      if (foundNode.children) {
        currToc = foundNode.children;
      } else {
        break;
      }
    } else {
      break;
    }
  }
  return tags;
};

const Widget = () => {
  const { root, path } = useParams();
  const navigate = useNavigate();

  // 1. 基础数据状态（必须保留的 State）
  const [tocData, setTocData] = useState<IPaliBookListResponse[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [openPara, setOpenPara] = useState({ book: 0, para: 0 });
  const [drawerTitle, setDrawerTitle] = useState("");

  // 2. 派生状态 (Derived State) - 直接计算，不使用 useState
  // 这种写法确保了数据流的单向性：URL -> 变量 -> UI
  const bookRoot = root || localStorage.getItem("pali_path_root") || "default";
  const bookPath = useMemo(() => (path ? path.split("_") : []), [path]);
  const bookTag = useMemo(() => getTagByPath(path, tocData), [path, tocData]);

  const userBookRoot = localStorage.getItem("pali_path_root") || "default";
  console.log("tipitaka root", root, userBookRoot);
  if (!root) {
    navigate("/workspace/tipitaka/" + userBookRoot, { replace: true });
  } else {
    localStorage.setItem("pali_path_root", userBookRoot);
  }
  // 3. 处理纯粹的副作用：重定向
  useEffect(() => {
    if (typeof root === "undefined") {
      navigate("/workspace/tipitaka/" + bookRoot, { replace: true });
    } else {
      localStorage.setItem("pali_path_root", root);
    }
  }, [root, bookRoot, navigate]);

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
                    onRootChange={(newRoot: string) =>
                      navigate("/workspace/tipitaka/" + newRoot)
                    }
                    onChange={(_, newPath) => {
                      // 只需要改变 URL，剩下的交给 useMemo 自动计算
                      const pathStr = newPath?.join("_").toLowerCase();
                      navigate(`/workspace/tipitaka/${bookRoot}/${pathStr}`);
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
                  navigate(
                    `/workspace/tipitaka/${bookRoot}/${e.path.join("_")}`
                  );
                }}
                onTocLoad={setTocData} // 直接设置，简化写法
              />
              <PaliChapterListByTag
                tag={bookTag}
                onChapterClick={(e: IChapterClickEvent) => {
                  if (!e.event.ctrlKey) {
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
        onClose={() => setIsModalOpen(false)}
        size="large"
        styles={{
          wrapper: {
            minWidth: 736,
            maxWidth: "100%",
          },
          body: {
            overflowY: "auto", // 滚动通常应该在 body 层
          },
        }}
        footer={null}
      >
        <BookViewer chapter={openPara} />
      </Drawer>
    </>
  );
};

export default Widget;
