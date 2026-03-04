import { useParams } from "react-router";

import AnthologyTocEdit from "../../../components/anthology/AnthologyTocEdit";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const user = useAppSelector(currentUser);

  return (
    <>
      <AnthologyTocEdit id={id} editorStudioName={user?.realName} />
    </>
  );
};

export default Widget;
