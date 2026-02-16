import { useIntl } from "react-intl";
import { Button } from "antd";
import { Link } from "react-router-dom";

const ToLibraryWidget = () => {
  const intl = useIntl();

  return (
    <>
      <Link to="/palicanon/list">
        <Button
          type="primary"
          style={{
            paddingLeft: 18,
            paddingRight: 18,
            backgroundColor: "#52974e",
          }}
        >
          {intl.formatMessage({
            id: "columns.library.title",
          })}
        </Button>
      </Link>
    </>
  );
};

export default ToLibraryWidget;
