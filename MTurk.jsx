/**
 *  This component adds a submit button that handles telling MTurk that the
 *  HIT is complete.
 *
 *  Use:
 *
 *  Somewhere in your app, render this component:
 *
 *    <MTurk />
 *
 *  In the same file you will need to include this import:
 *
 *    import MTurk from './MTurk.jsx';
 *
 *  That's it! Now there will be a submit button which will tell MTurk that the
 *  HIT is complete. If you need to check something before allowing the user to
 *  do that, put the code in handleSubmit below.
 *
 *
 *  Explanation of code:
 *
 *  There are a few pieces that work together below.
 *  1. gup, a function that gets a URL parameter.
 *  2. MTurk, a react component with a constructor that uses the function to
 *  save the relevant mturk parameters as internal state.
 *  3. render, a function that creates a form with hidden fields that hold
 *  the parameters.
 *  4. handleSubmit, a function that checks if the current values of the
 *  parameters are valid. If they are it lets the submit happen. If they are
 *  not it cancels it.
 **/

import React, { Component } from 'react';
import { Meteor } from 'meteor/meteor';

// gup = Get URL Parameter
//
// Note, there is a nice native solution (URLSearchParams) that does not work
// in internet explorer :( but maybe soon?
// https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/8993198/
// there are polyfill solutions, e.g.
// https://github.com/jerrybendy/url-search-params-polyfill
// Alternatively, there are libraries for query string handling:
// https://www.npmjs.com/package/qs
// https://www.npmjs.com/package/query-string
// For now this does what we need and is lightweight
function gup(name) {
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if (results == null) {
      return "";
  } else {
    return unescape(results[1]);
  }
}

export default class MTurk extends Component {
  constructor(props) {
    super(props);
    this.state = {
      assignmentId: gup("assignmentId"),
      workerId: gup("workerId"),
      hitId: gup("hitId"),
      submitURL: gup("turkSubmitTo")
    };

    this.handleSubmit = this.handleSubmit.bind(this);
  }

  handleSubmit(e) {
    // Validate
    if (
      this.state.assignmentId == "" ||
      this.state.workerId == "" ||
      this.state.hitId == "" ||
      this.state.assignmentId == "ASSIGNMENT_ID_NOT_AVAILABLE"
    ) {
      console.log("failed");
      e.preventDefault();
      e.stopPropagation();
    }
  }

  render() {
    return (
      <div>
        <form 
          id="mturk_form"
          onSubmit={this.handleSubmit}
          action={this.state.submitURL + "/mturk/externalSubmit"}
          method="post"
        >
          <input type='hidden' name='assignmentId' value={this.state.assignmentId} />
          <input type='hidden' name='workerId' value={this.state.workerId} />
          <input type='hidden' name='hitId' value={this.state.hitId} />
          <input type="submit" value="Submit HIT" />
        </form>
      </div>
    );
  }
}
