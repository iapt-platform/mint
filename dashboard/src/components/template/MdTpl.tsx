interface IWidgetMdTpl {
	name?: string;
}
const Widget = ({ name }: IWidgetMdTpl) => {
	return <span>tpl name: {name}</span>;
};

export default Widget;
