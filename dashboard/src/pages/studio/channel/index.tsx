import { useParams ,Link} from "react-router-dom";
import { useIntl } from "react-intl";
import { Space,Layout } from "antd";
import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";

const {  Content } = Layout;

const Widget = () => {
	const intl = useIntl();//i18n
	const { studioname } = useParams();//url 参数
	const linkEdit = `/studio/${studioname}/channel/edit/12345`;
  return (
	<Layout>
	<HeadBar/>
	<Layout>
		<LeftSider selectedKeys="channel"/>
		<Content>
		<h2>studio/{studioname}/{intl.formatMessage({ id: "columns.studio.channel.title" })}/版本列表</h2>
		<div>
			<Space>
				<Link to={linkEdit}> channel1 edit </Link>
			</Space>
		</div>		
	  </Content>
	</Layout>
	<Footer/>
</Layout>

  );
};

export default Widget;
