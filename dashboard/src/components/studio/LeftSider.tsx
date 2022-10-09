import { Link } from "react-router-dom";
import { Space } from "antd";
import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";

const Widget = () => {
	//Library head bar
	const intl = useIntl();//i18n
	const { studioname } = useParams();
	// TODO
	const linkPalicanon = "/studio/"+studioname+"/palicanon";
	const linkRecent = "/studio/"+studioname+"/recent";
	const linkChannel = "/studio/"+studioname+"/channel";
	const linkGroup = "/studio/"+studioname+"/group";
	const linkUserdict = "/studio/"+studioname+"/dict";
	const linkTerm = "/studio/"+studioname+"/term";
	const linkArticle = "/studio/"+studioname+"/article";
	const linkAnthology = "/studio/"+studioname+"/anthology";
	const linkAnalysis = "/studio/"+studioname+"/analysis";
  return (
  <div>
	<Space>
		<Link to={linkPalicanon}>
			{intl.formatMessage({ id: "columns.studio.palicanon.title" })}
		</Link>
		<Link to={linkRecent}>
			{intl.formatMessage({ id: "columns.studio.recent.title" })}
		</Link>
		<Link to={linkChannel}>
			{intl.formatMessage({ id: "columns.studio.channel.title" })}
		</Link>
		<Link to={linkGroup}>
			{intl.formatMessage({ id: "columns.studio.group.title" })}
		</Link>
		<Link to={linkUserdict}>
			{intl.formatMessage({ id: "columns.studio.userdict.title" })}
		</Link>
		<Link to={linkTerm}>
			{intl.formatMessage({ id: "columns.studio.term.title" })}
		</Link>
		<Link to={linkArticle}>
			{intl.formatMessage({ id: "columns.studio.article.title" })}
		</Link>
		<Link to={linkAnthology}>
			{intl.formatMessage({ id: "columns.studio.anthology.title" })}
		</Link>
		<Link to={linkAnalysis}>
			{intl.formatMessage({ id: "columns.studio.analysis.title" })}
		</Link>
	</Space>

  </div>
  );
};

export default Widget;
