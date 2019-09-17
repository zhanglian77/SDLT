// @flow

import React, {Component} from "react";
import {connect} from "react-redux";
import type {RootState} from "../../store/RootState";
import {Dispatch} from "redux";
import Header from "../Header/Header";
import Footer from "../Footer/Footer";
import type {User} from "../../types/User";
import {loadCurrentUser} from "../../actions/user";
import {loadSiteTitle} from "../../actions/siteConfig";
import type {JiraTicket, SecurityComponent} from "../../types/SecurityComponent";
import ComponentSelection from "./ComponentSelection";
import {
  addSelectedComponent,
  createJIRATickets,
  loadAvailableComponents,
  removeSelectedComponent
} from "../../actions/componentSelection";
import URLUtil from "../../utils/URLUtil";
import ComponentSelectionReview from "./ComponentSelectionReview";
import DarkButton from "../Button/DarkButton";

const mapStateToProps = (state: RootState) => {
  return {
    siteTitle: state.siteConfigState.siteTitle,
    currentUser: state.currentUserState.user,
    availableComponents: state.componentSelectionState.availableComponents,
    selectedComponents: state.componentSelectionState.selectedComponents,
    viewMode: state.componentSelectionState.viewMode,
    jiraTickets: state.componentSelectionState.jiraTickets
  };
};

const mapDispatchToProps = (dispatch: Dispatch, props: *) => {
  return {
    dispatchLoadDataAction() {
      dispatch(loadCurrentUser());
      dispatch(loadSiteTitle());
      dispatch(loadAvailableComponents())
    },
    dispatchAddComponentAction(id: string) {
      dispatch(addSelectedComponent(id));
    },
    dispatchRemoveComponentAction(id: string) {
      dispatch(removeSelectedComponent(id))
    },
    dispatchCreateJIRATicketsAction(jiraKey: string) {
      dispatch(createJIRATickets(jiraKey))
    }
  };
};

type OwnProps = {
  taskId: string,
  componentTarget: string,
}

type ReduxProps = {
  siteTitle?: string,
  currentUser?: User | null,
  availableComponents?: Array<SecurityComponent>,
  selectedComponents?: Array<SecurityComponent>,
  viewMode?: "edit" | "review",
  jiraTickets?: Array<JiraTicket>,
  dispatchLoadDataAction?: () => void,
  dispatchCreateJIRATicketsAction?: (jiraKey: string) => void,
  dispatchAddComponentAction?: (id: string) => void,
  dispatchRemoveComponentAction?: (id: string) => void,
}

type Props = OwnProps & ReduxProps;

class ComponentSelectionStandaloneContainer extends Component<Props> {

  componentDidMount() {
    const {dispatchLoadDataAction} = {...this.props};
    dispatchLoadDataAction();
  }

  render() {
    const {
      siteTitle,
      currentUser,
      availableComponents,
      selectedComponents,
      dispatchAddComponentAction,
      dispatchRemoveComponentAction,
      dispatchCreateJIRATicketsAction,
      viewMode,
      jiraTickets,
      componentTarget
    } = {...this.props};

    if (!currentUser) {
      return null;
    }

    let body = null;
    switch (viewMode) {
      case "edit":
        body = (
          <ComponentSelection
            availableComponents={availableComponents}
            selectedComponents={selectedComponents}
            componentTarget={componentTarget}
            isStandaloneTask={true}
            createJIRATickets={(jiraKey) => {
              dispatchCreateJIRATicketsAction(jiraKey);
            }}
            removeComponent={(id) => {
              dispatchRemoveComponentAction(id);
            }}
            addComponent={(id) => {
              dispatchAddComponentAction(id);
            }}
            finishWithSelection={() => {
              URLUtil.redirectToHome()
            }}
          />
        );
        break;
      case "review":
        body = (
          <ComponentSelectionReview
            selectedComponents={selectedComponents}
            jiraTickets={jiraTickets}
          >
            <div className="buttons">
              <DarkButton title="BACK TO HOME" onClick={() => {URLUtil.redirectToHome()}} />
            </div>
          </ComponentSelectionReview>
        );
    }

    return (
      <div className="ComponentSelectionContainer">
        <Header title="Component Selection" subtitle={siteTitle} username={currentUser.name}/>
        {body}
        <Footer/>
      </div>
    );
  }
}

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(ComponentSelectionStandaloneContainer);
