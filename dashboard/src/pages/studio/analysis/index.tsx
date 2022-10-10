import { useParams ,Link} from "react-router-dom";
import { useIntl } from "react-intl";
import { Layout,Space } from "antd";
import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";
const {  Content } = Layout;

const Widget = () => {
	const intl = useIntl();//i18n
	const { studioname } = useParams();//url 参数
  return (
    <Layout>
		<HeadBar/>
		<Layout>
			<LeftSider selectedKeys="analysis"/>
			<Content>
				<h2>studio/{studioname}/{intl.formatMessage({ id: "columns.studio.analysis.title" })}/行为分析首页</h2>
				<Space>
					<Link to=""> </Link>
				</Space>
			</Content>
		</Layout>
      <Footer/>
    </Layout>
  );
};

export default Widget;
