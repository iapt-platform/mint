import { Layout, Result } from "antd";
import { Outlet } from "react-router-dom";
import LeftSider from "../../components/admin/LeftSider";
import HeadBar from "../../components/studio/HeadBar";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);

  return (
    <div>
      <HeadBar />
      <Layout>
        <LeftSider />
        {user?.roles?.includes("root") ||
        user?.roles?.includes("administrator") ? (
          <Outlet />
        ) : (
          <Result
            status="403"
            title="403"
            subTitle="Sorry, you are not authorized to access this page."
          />
        )}
      </Layout>
    </div>
  );
};

export default Widget;
