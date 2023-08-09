import { Button, Card, Typography } from "antd";

import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { GithubOutlined } from "@ant-design/icons";

import bg_png from "../../../assets/library/images/download_bg.png";
import Marked from "../../../components/general/Marked";
const { Paragraph } = Typography;

const ChapterNewWidget = () => {
  const [github, setGithub] = useState<string>("loading");
  const githubLink =
    "https://raw.githubusercontent.com/ariyamaggika/wikipali-app/master/version.txt";
  const giteeLink =
    "https://gitee.com/wolf96/wikipali-app/raw/main/version.txt";
  const giteeRelease = "https://gitee.com/wolf96/wikipali-app/releases";
  useEffect(() => {
    fetch(githubLink, {
      method: "GET",
      mode: "cors",
    })
      .then((res) => {
        return res.text();
      })
      .then((text) => {
        console.log("获取的结果", text);
        const link = text.replace(
          /https(.+?)\.apk/g,
          "- [https$1.apk](https$1.apk)"
        );
        setGithub(link.replaceAll("\n", "\n\n"));
        return text;
      })
      .catch((err) => {
        console.log("请求错误", err);
      });
  }, []);
  return (
    <Paragraph>
      <div>
        <img alt="code" src={bg_png} />
      </div>
      <Card
        title={"中国大陆"}
        style={{ margin: 10, borderRadius: 8 }}
        hoverable
      >
        <Paragraph>
          <Link to={giteeRelease} target="_blank">
            Gitee
          </Link>
        </Paragraph>
      </Card>
      <Card
        title={"其他地区"}
        style={{ margin: 10, borderRadius: 8 }}
        hoverable
      >
        <Paragraph>
          <Button icon={<GithubOutlined />} type="text">
            <Link
              to="https://github.com/gohugoio/hugo/releases"
              target="_blank"
            >
              Github
            </Link>
          </Button>
        </Paragraph>

        <Paragraph>
          <Marked text={github} />
        </Paragraph>
      </Card>
    </Paragraph>
  );
};

export default ChapterNewWidget;
