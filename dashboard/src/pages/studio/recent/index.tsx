import { useParams ,Link} from "react-router-dom";
import { useIntl } from "react-intl";
import { Space } from "antd";
import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";

const Widget = () => {
	const intl = useIntl();//i18n
	const { studioname } = useParams();//url 参数
  return (
    <div>
		<HeadBar/>
		<LeftSider/>
      <h2>studio/{studioname}/{intl.formatMessage({ id: "columns.studio.recent.title" })}</h2>
      <div>
		<Space>
			<Link to=""> </Link>
		</Space>
      </div>
      <Footer/>
    </div>
  );
};

export default Widget;
