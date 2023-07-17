import { useIntl } from "react-intl";
import { Popover, Avatar } from "antd";
import { IStudio } from "./StudioName";
import { Link } from "react-router-dom";

interface IWidget {
  studio?: IStudio;
  children?: JSX.Element;
}
const StudioCardWidget = ({ studio, children }: IWidget) => {
  const intl = useIntl();

  return (
    <Popover
      content={
        <>
          <div style={{ display: "flex" }}>
            <div style={{ paddingRight: 8 }}>
              <Avatar style={{ backgroundColor: "#87d068" }} size="small">
                {studio?.nickName?.slice(0, 1)}
              </Avatar>
            </div>
            <div>
              <div>{studio?.nickName}</div>
              <div>
                <Link
                  to={`/blog/${studio?.studioName}/overview`}
                  target="_blank"
                >
                  {intl.formatMessage({
                    id: "columns.library.blog.label",
                  })}
                </Link>
              </div>
            </div>
          </div>
        </>
      }
      placement="bottomRight"
    >
      {children}
    </Popover>
  );
};

export default StudioCardWidget;
