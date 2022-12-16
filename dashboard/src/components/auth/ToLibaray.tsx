import { useIntl } from "react-intl";
import { Button } from "antd";
import { Link } from "react-router-dom";

const Widget = () => {
  const intl = useIntl();

  return (
    <>
      <Link to="/palicanon/list">
        <Button type="primary">
          {intl.formatMessage({
            id: "columns.library.title",
          })}
        </Button>
      </Link>
    </>
  );
};

export default Widget;
