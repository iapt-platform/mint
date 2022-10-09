import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";


const Widget = () => {
	const intl = useIntl();
	const { studioname,groupid } = useParams();//url 参数
  return (
    <div>
		<HeadBar/>
		<LeftSider/>
      <h2>studio/{studioname}/{intl.formatMessage({ id: "columns.studio.channel.title" })}/show/{groupid}</h2>
      <div>
		群组详情
      </div>
      <Footer/>
    </div>
  );
};

export default Widget;
