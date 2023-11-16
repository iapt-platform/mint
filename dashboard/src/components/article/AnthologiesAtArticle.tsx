import { Space, Typography, message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IArticleMapListResponse } from "../api/Article";

const { Link, Paragraph } = Typography;
interface IList {
  key?: string;
  label?: string;
}
interface IWidget {
  articleId?: string;
  anthologyId?: string | null;
  onClick?: Function;
}
const AnthologiesAtArticleWidget = ({
  articleId,
  anthologyId,
  onClick,
}: IWidget) => {
  const [list, setList] = useState<IList[]>();
  useEffect(() => {
    //查询这个article 有多少文集
    let url = `/v2/article-map?view=article&id=${articleId}`;
    console.log("url", url);
    get<IArticleMapListResponse>(url).then((json) => {
      if (json.ok) {
        const anthologies: IList[] = json.data.rows.map((item) => {
          return {
            key: item.collection?.id,
            label: item.collection?.title,
          };
        });
        console.log("anthologies", anthologies);
        setList(anthologies.filter((value) => value.key !== anthologyId));
      } else {
        message.error("获取文集列表失败");
      }
    });
  }, [articleId]);

  let title = "";
  if (anthologyId) {
    title = "其他文集";
  } else {
    title = "文集列表";
  }

  return (
    <Paragraph style={{ display: list && list.length > 0 ? "block" : "none" }}>
      <Space>
        {title}
        {list?.map((item, index) => {
          return (
            <Link
              key={index}
              onClick={(e) => {
                if (typeof onClick !== "undefined") {
                  onClick(item.key, e);
                }
              }}
            >
              {item.label}
            </Link>
          );
        })}
      </Space>
    </Paragraph>
  );
};

export default AnthologiesAtArticleWidget;
