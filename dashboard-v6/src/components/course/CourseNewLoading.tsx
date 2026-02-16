import { Skeleton } from "antd";

const CourseNewLoading = () => {
  return (
    <div style={{ display: "flex", width: "100%" }}>
      {[1, 1, 1, 1].map((item, id) => {
        return (
          <div style={{ height: 400, flex: 2 }}>
            <Skeleton.Image active={true} />
            <Skeleton title={{ width: 40 }} paragraph={{ rows: 2 }} active />
          </div>
        );
      })}
    </div>
  );
};

export default CourseNewLoading;
