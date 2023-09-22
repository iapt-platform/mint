import { Button } from "antd";
import { Link } from "react-router-dom";
import { SearchOutlined } from "@ant-design/icons";

const SearchButtonWidget = () => {
  return (
    <Button type="text" size="small">
      <Link to="/search/home" target={"_blank"}>
        <SearchOutlined style={{ color: "white" }} />
      </Link>
    </Button>
  );
};

export default SearchButtonWidget;
