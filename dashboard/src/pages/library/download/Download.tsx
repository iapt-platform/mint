import { Divider, Typography } from "antd";
import { Link } from "react-router-dom";
import bg_png from "../../../assets/library/images/download_bg.png";
const { Paragraph } = Typography;

const ChapterNewWidget = () => {
  return (
    <Paragraph>
      <div>
        <img alt="code" src={bg_png} />
      </div>
      <Divider>中国大陆</Divider>
      <Paragraph>
        <Link to="https://gitee.com/" target="_blank">
          Gitee
        </Link>
      </Paragraph>
      <Divider>其他地区</Divider>
      <Paragraph>
        <Link to="https://github.com/gohugoio/hugo/releases" target="_blank">
          Github
        </Link>
      </Paragraph>
    </Paragraph>
  );
};

export default ChapterNewWidget;
