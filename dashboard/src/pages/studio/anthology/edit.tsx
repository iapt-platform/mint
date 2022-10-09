import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";

const Widget = () => {
	const intl = useIntl();
	const { studioname,anthology_id } = useParams();//url 参数
  return (
    <div>
		<HeadBar/>
		<LeftSider/>
      <h2>studio/{studioname}/{intl.formatMessage({ id: "columns.studio.anthology.title" })}/anthology/{anthology_id}</h2>
      <div>
		
      </div>
      <Footer/>
    </div>
  );
};

export default Widget;
