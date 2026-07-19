import FieldText from './field-text';
import FieldTextarea from './field-textarea';
import FieldSelect from './field-select';
import FieldColor from './field-color';
import FieldCheckbox from './field-checkbox';
import FieldButtonGroup from './field-button-group';
import FieldUnit from './field-unit';
import FieldTypography from './field-typography';
import FieldBorder from './field-border';
import FieldDimension from './field-dimension';
import FieldSpacing from './field-spacing';
import FieldLink from './field-link';
import FieldPhoto from './field-photo';
import FieldIcon from './field-icon';
import FieldCode from './field-code';
import FieldHidden from './field-hidden';
import FieldAlign from './field-align';
import FieldFont from './field-font';
import FieldShadow from './field-shadow';
import FieldGradient from './field-gradient';
import FieldAnimation from './field-animation';
import FieldMultiplePhotos from './field-multiple-photos';
import FieldVideo from './field-video';
import FieldOrdering from './field-ordering';
import FieldSuggest from './field-suggest';
import FieldRaw from './field-raw';
import FieldForm from './field-form';

const registry = {
	text: FieldText,
	textarea: FieldTextarea,
	editor: FieldTextarea,
	select: FieldSelect,
	color: FieldColor,
	checkbox: FieldCheckbox,
	'button-group': FieldButtonGroup,
	unit: FieldUnit,
	typography: FieldTypography,
	border: FieldBorder,
	dimension: FieldDimension,
	spacing: FieldSpacing,
	link: FieldLink,
	photo: FieldPhoto,
	icon: FieldIcon,
	code: FieldCode,
	hidden: FieldHidden,
	align: FieldAlign,
	font: FieldFont,
	shadow: FieldShadow,
	gradient: FieldGradient,
	animation: FieldAnimation,
	'multiple-photos': FieldMultiplePhotos,
	video: FieldVideo,
	ordering: FieldOrdering,
	suggest: FieldSuggest,
	raw: FieldRaw,
	form: FieldForm,
};

export default registry;
