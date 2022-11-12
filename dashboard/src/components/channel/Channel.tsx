export interface IChannel {
	name: string;
	id: string;
}
const Widget = ({ name, id }: IChannel) => {
	return <span>{name}</span>;
};

export default Widget;
