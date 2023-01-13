import React from 'react'

function Alerts({ data }) {
    return (
        <>
            <div id="bb-toast" className="min-toast add-item" data-use="cartAdd" style={{ bottom: '76px', left: '150px' }}>Item Added</div>
            <div id="bb-toast" className="select_items" style={{ bottom: '76px' }}>You must select an option for this product</div>

            <div className="bxp-building-bg" id="cartAnimation" style={{ display: 'none' }}>
                <div className="bxp-building">
                    <div className="bxp-building-img">
                        <img src={data.url + '/gifs/' + data.builderSettings.settings.display_settings.gif} width="300px" />
                    </div>
                    <h3>We are building your box.</h3>
                    <p>This may take a few seconds...</p>
                </div>
            </div>

            <div id="bb-discount-toast" className="" style={{ bottom: '116px' }}>Add 1 more item(s) to receive 12.00% off.</div>

        </>
    )
}

export default Alerts