import TaskList from "../../../components/task/TaskList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  return currUser ? (
    <>
      <title>task hall</title>
      <TaskList
        studioName={currUser.realName}
        status={["published"]}
        filters={[
          {
            field: "assignees_id",
            operator: "null",
            value: "",
          },
        ]}
      />
    </>
  ) : (
    <>未登录</>
  );
};

export default Widget;
