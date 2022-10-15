import { Cascader } from "antd";
import { useIntl } from "react-intl";

interface CascaderOption {
	value: string | number;
	label: string;
	children?: CascaderOption[];
}

const Widget = () => {
	const intl = useIntl();
	const case8 = [
		{
			value: "nom",
			label: intl.formatMessage({ id: "dict.fields.type.nom.label" }),
		},
		{
			value: "acc",
			label: intl.formatMessage({ id: "dict.fields.type.acc.label" }),
		},
		{
			value: "gen",
			label: intl.formatMessage({ id: "dict.fields.type.gen.label" }),
		},
		{
			value: "dat",
			label: intl.formatMessage({ id: "dict.fields.type.dat.label" }),
		},
		{
			value: "inst",
			label: intl.formatMessage({ id: "dict.fields.type.inst.label" }),
		},
		{
			value: "abl",
			label: intl.formatMessage({ id: "dict.fields.type.abl.label" }),
		},
		{
			value: "voc",
			label: intl.formatMessage({ id: "dict.fields.type.voc.label" }),
		},
	];
	const case2 = [
		{
			value: "sg",
			label: intl.formatMessage({ id: "dict.fields.type.sg.label" }),
			children: case8,
		},
		{
			value: "pl",
			label: intl.formatMessage({ id: "dict.fields.type.pl.label" }),
			children: case8,
		},
	];
	const case3 = [
		{
			value: "m",
			label: intl.formatMessage({ id: "dict.fields.type.m.label" }),
			children: case2,
		},
		{
			value: "nt",
			label: intl.formatMessage({ id: "dict.fields.type.nt.label" }),
			children: case2,
		},
		{
			value: "f",
			label: intl.formatMessage({ id: "dict.fields.type.f.label" }),
			children: case2,
		},
	];
	const options: CascaderOption[] = [
		{
			value: ".n.",
			label: intl.formatMessage({ id: "dict.fields.type.n.label" }),
			children: case3,
		},
		{
			value: ".ti.",
			label: intl.formatMessage({ id: "dict.fields.type.ti.label" }),
			children: case3,
		},
		{
			value: ".v.",
			label: intl.formatMessage({ id: "dict.fields.type.v.label" }),
			children: case3,
		},
	];

	return <Cascader options={options} placeholder="Please select case" />;
};

export default Widget;
