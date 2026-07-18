export const initialState = {
	activeTab: 'modules',
	editingNodeId: null,
	hasUnsaved: false,
	notices: [],
	layout: [],
	loadingLayout: true,
};

export function reducer( state, action ) {
	switch ( action.type ) {
		case 'SET_TAB':
			return { ...state, activeTab: action.tab, editingNodeId: null };
		case 'EDIT_NODE':
			return { ...state, activeTab: 'settings', editingNodeId: action.nodeId };
		case 'BACK_TO_MODULES':
			return { ...state, activeTab: 'modules', editingNodeId: null };
		case 'SET_UNSAVED':
			return { ...state, hasUnsaved: action.value };
		case 'SET_LAYOUT':
			return { ...state, layout: action.layout };
		case 'SET_LAYOUT_LOADED':
			return { ...state, loadingLayout: false };
		case 'ADD_NOTICE':
			return {
				...state,
				notices: [
					...state.notices,
					{ id: action.id, message: action.message, type: action.noticeType, textOnly: action.textOnly },
				],
			};
		case 'REMOVE_NOTICE':
			return {
				...state,
				notices: state.notices.filter( ( n ) => n.id !== action.id ),
			};
		default:
			return state;
	}
}
