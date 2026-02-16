import { ITaskData } from "../api/task";
import User, { IUser } from "../auth/User";

const Executors = ({
  data,
  all,
}: {
  data: ITaskData;
  all: readonly ITaskData[];
}) => {
  const children = all.filter((value) => value.parent_id === data.id);
  let executors: IUser[] = data.executor ? [data.executor] : [];
  children.forEach((task) => {
    executors = executors.concat(task.executor ?? []);
  });
  return (
    <div>
      {executors.map((item, id) => {
        return <User {...item} key={id} showName={executors.length === 1} />;
      })}
    </div>
  );
};

export default Executors;
