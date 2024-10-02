import { Button } from "antd";
import { Link } from "react-router-dom";
import { SearchOutlined } from "@ant-design/icons";

interface IWidget {
  style?: React.CSSProperties;
}
const SearchButtonWidget = ({ style }: IWidget) => {
  return (
    <Button type="text" size="small" style={style}>
      <Link to="/search/home" target={"_blank"}>
        <SearchOutlined style={{ color: "white" }} />
      </Link>
    </Button>
  );
};

export default SearchButtonWidget;
