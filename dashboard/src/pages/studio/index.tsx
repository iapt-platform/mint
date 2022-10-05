import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Space } from "antd";

const Widget = () => {
	const intl = useIntl();
	const { studioid } = useParams();

  return (
    <div>
      <div>studio index{intl.formatMessage({ id: "title.channel" })}</div>
      <div>
        <div>
          <Space>
            <Link to="/">Home</Link>
            <Link to="/community/myread">{studioid}</Link>
          </Space>
        </div>
      </div>
      <div>底部区域</div>
    </div>
  );
};

export default Widget;
