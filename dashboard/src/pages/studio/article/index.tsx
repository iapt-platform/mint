import { useParams ,Link} from "react-router-dom";
import { useIntl } from "react-intl";
import { Space } from "antd";
import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";

const Widget = () => {
	const intl = useIntl();//i18n
	const { studioname } = useParams();//url 参数
	const linkEdit = `/studio/${studioname}/article/edit/12345`;
  return (
    <div>
		<HeadBar/>
		<LeftSider/>
      <h2>studio/{studioname}/{intl.formatMessage({ id: "columns.studio.article.title" })}/文章列表</h2>
      <div>
		<Space>
			<Link to={linkEdit}> article edit </Link>
		</Space>
      </div>
      <Footer/>
    </div>
  );
};

export default Widget;
